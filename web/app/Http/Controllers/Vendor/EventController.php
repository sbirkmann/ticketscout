<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = auth()->user()->events()->with('location', 'category')->latest()->get();
        return Inertia::render('Vendor/Events/Index', ['events' => $events]);
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:publish,draft,delete',
            'event_ids' => 'required|array',
            'event_ids.*' => 'exists:events,id',
        ]);

        $events = auth()->user()->events()->whereIn('id', $validated['event_ids'])->get();

        foreach ($events as $event) {
            if ($validated['action'] === 'publish') {
                $event->update(['status' => 'published']);
            } elseif ($validated['action'] === 'draft') {
                $event->update(['status' => 'draft']);
            } elseif ($validated['action'] === 'delete') {
                // Should potentially check if tickets are already sold before deleting
                $event->delete();
            }
        }

        return back()->with('success', 'Bulk-Aktion erfolgreich ausgeführt.');
    }

    public function create()
    {
        $locations = \App\Models\Location::where('vendor_id', auth()->id())
            ->orWhere('is_global', true)
            ->get();
            
        $categories = \App\Models\EventCategory::all();
        $artists = \App\Models\Artist::all(); // Assuming artists are global or vendor specific
        $templates = auth()->user()->ticketTemplates;

        return Inertia::render('Vendor/Events/Create', [
            'locations' => $locations,
            'categories' => $categories,
            'artists' => $artists,
            'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_category_id' => 'required|exists:event_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'ticket_template_id' => 'nullable|exists:ticket_templates,id',
            'new_location_name' => 'required_without:location_id|string|max:255|nullable',
            'new_location_address' => 'required_with:new_location_name|string|max:255|nullable',
            'new_location_zip' => 'required_with:new_location_name|string|max:20|nullable',
            'new_location_city' => 'required_with:new_location_name|string|max:100|nullable',
            'new_location_country' => 'required_with:new_location_name|string|max:2|nullable',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'image' => 'nullable|image|max:2048',
            'hero_background' => 'nullable|image|max:2048',
            'gallery_images.*' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published',
            'tags' => 'nullable|string',
            'artist_ids' => 'nullable|array',
            'artist_ids.*' => 'exists:artists,id',
            'show_remaining_tickets' => 'boolean',
        ]);

        $locationId = $validated['location_id'];

        if (!$locationId && $validated['new_location_name']) {
            $location = Location::create([
                'name' => $validated['new_location_name'],
                'slug' => Str::slug($validated['new_location_name']) . '-' . uniqid(),
                'address' => $validated['new_location_address'],
                'zip' => $validated['new_location_zip'],
                'city' => $validated['new_location_city'],
                'country' => $validated['new_location_country'],
                'is_global' => false,
            ]);
            $locationId = $location->id;
        }

        $event = new Event([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . uniqid(),
            'event_category_id' => $validated['event_category_id'],
            'location_id' => $locationId,
            'ticket_template_id' => $validated['ticket_template_id'] ?? null,
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'show_remaining_tickets' => $validated['show_remaining_tickets'] ?? true,
            'tags' => $validated['tags'] ? array_map('trim', explode(',', $validated['tags'])) : [],
        ]);

        if ($request->hasFile('image')) {
            $event->image_path = $request->file('image')->store('events', 'public');
        }
        if ($request->hasFile('hero_background')) {
            $event->hero_background_path = $request->file('hero_background')->store('events/hero', 'public');
        }
        
        $galleryPaths = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $galleryPaths[] = $file->store('events/gallery', 'public');
            }
            $event->gallery_images = $galleryPaths;
        }

        auth()->user()->events()->save($event);

        if (!empty($validated['artist_ids'])) {
            $event->artists()->sync($validated['artist_ids']);
        }

        return redirect()->route('vendor.events.show', $event)->with('success', 'Event erstellt.');
    }

    public function show(Event $event)
    {
        if ($event->vendor_id !== auth()->id()) abort(403);
        $event->load(['ticketCategories', 'addons.ticketCategories', 'location', 'category', 'waitlists' => function($q) {
            $q->orderBy('created_at', 'desc');
        }]);
        return Inertia::render('Vendor/Events/Show', ['event' => $event]);
    }

    public function edit(Event $event)
    {
        if ($event->vendor_id !== auth()->id()) abort(403);
        $event->load('artists');
        return Inertia::render('Vendor/Events/Edit', [
            'event' => $event,
            'locations' => Location::orderBy('name')->get(),
            'categories' => EventCategory::orderBy('name')->get(),
            'artists' => \App\Models\Artist::orderBy('name')->get(),
            'templates' => auth()->user()->ticketTemplates,
        ]);
    }

    public function update(Request $request, Event $event)
    {
        if ($event->vendor_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_category_id' => 'required|exists:event_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'ticket_template_id' => 'nullable|exists:ticket_templates,id',
            'new_location_name' => 'required_without:location_id|string|max:255|nullable',
            'new_location_address' => 'required_with:new_location_name|string|max:255|nullable',
            'new_location_zip' => 'required_with:new_location_name|string|max:20|nullable',
            'new_location_city' => 'required_with:new_location_name|string|max:100|nullable',
            'new_location_country' => 'required_with:new_location_name|string|max:2|nullable',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'image' => 'nullable|image|max:2048',
            'hero_background' => 'nullable|image|max:2048',
            'gallery_images.*' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published',
            'tags' => 'nullable|string',
            'artist_ids' => 'nullable|array',
            'artist_ids.*' => 'exists:artists,id',
            'show_remaining_tickets' => 'boolean',
        ]);

        $locationId = $validated['location_id'];

        if (!$locationId && $validated['new_location_name']) {
            $location = Location::create([
                'name' => $validated['new_location_name'],
                'slug' => Str::slug($validated['new_location_name']) . '-' . uniqid(),
                'address' => $validated['new_location_address'],
                'zip' => $validated['new_location_zip'],
                'city' => $validated['new_location_city'],
                'country' => $validated['new_location_country'],
                'is_global' => false,
            ]);
            $locationId = $location->id;
        }

        $event->title = $validated['title'];
        $event->event_category_id = $validated['event_category_id'];
        $event->location_id = $locationId;
        $event->ticket_template_id = $validated['ticket_template_id'] ?? null;
        $event->description = $validated['description'];
        $event->start_date = $validated['start_date'];
        $event->end_date = $validated['end_date'];
        $event->status = $validated['status'];
        $event->show_remaining_tickets = $validated['show_remaining_tickets'] ?? true;
        $event->tags = $validated['tags'] ? array_map('trim', explode(',', $validated['tags'])) : [];

        if ($request->hasFile('image')) {
            $event->image_path = $request->file('image')->store('events', 'public');
        }
        if ($request->hasFile('hero_background')) {
            $event->hero_background_path = $request->file('hero_background')->store('events/hero', 'public');
        }
        
        if ($request->hasFile('gallery_images')) {
            $galleryPaths = is_array($event->gallery_images) ? $event->gallery_images : [];
            foreach ($request->file('gallery_images') as $file) {
                $galleryPaths[] = $file->store('events/gallery', 'public');
            }
            $event->gallery_images = $galleryPaths;
        }

        $event->save();

        if (isset($validated['artist_ids'])) {
            $event->artists()->sync($validated['artist_ids']);
        } else {
            $event->artists()->detach();
        }

        return redirect()->route('vendor.events.show', $event)->with('success', 'Event aktualisiert.');
    }

    public function destroy(Event $event)
    {
        if ($event->vendor_id !== auth()->id()) abort(403);
        $event->delete();
        return redirect()->route('vendor.events.index')->with('success', 'Event gelöscht.');
    }

    public function duplicate(Event $event)
    {
        if ($event->vendor_id !== auth()->id()) abort(403);
        
        $newEvent = $event->replicate(['slug', 'status', 'is_approved']);
        $newEvent->title = $event->title . ' (Kopie)';
        $newEvent->slug = Str::slug($event->title) . '-kopie-' . uniqid();
        $newEvent->status = 'draft';
        $newEvent->is_approved = false;
        
        // Link to parent or use current as parent
        $newEvent->parent_event_id = $event->parent_event_id ?? $event->id;
        $newEvent->save();

        // Duplicate ticket categories
        foreach ($event->ticketCategories as $category) {
            $newCategory = $category->replicate();
            $newEvent->ticketCategories()->save($newCategory);
        }

        // Duplicate addons
        foreach ($event->addons as $addon) {
            $newAddon = $addon->replicate();
            $newEvent->addons()->save($newAddon);
        }

        // Duplicate artists
        $newEvent->artists()->sync($event->artists->pluck('id'));

        return redirect()->route('vendor.events.edit', $newEvent)->with('success', 'Event wurde dupliziert. Bitte Datum anpassen.');
    }
    public function seating(Event $event)
    {
        if ($event->vendor_id !== auth()->id()) abort(403);
        
        if (!$event->seating_plan_id) {
            return redirect()->route('vendor.events.edit', $event->id)->with('error', 'Kein Saalplan für dieses Event ausgewählt.');
        }

        $event->load(['ticketCategories', 'seatingPlan']);
        $mappings = \App\Models\EventSeatingCategory::where('event_id', $event->id)->get();

        return Inertia::render('Vendor/Events/Seating', [
            'event' => $event,
            'seatingPlan' => $event->seatingPlan,
            'ticketCategories' => $event->ticketCategories,
            'mappings' => $mappings
        ]);
    }

    public function updateSeating(Request $request, Event $event)
    {
        if ($event->vendor_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'mappings' => 'required|array',
            'mappings.*.element_id' => 'required|string',
            'mappings.*.ticket_category_id' => 'nullable|exists:ticket_categories,id',
        ]);

        // Delete old mappings
        \App\Models\EventSeatingCategory::where('event_id', $event->id)->delete();

        // Insert new mappings
        $insertData = [];
        foreach ($validated['mappings'] as $mapping) {
            if ($mapping['ticket_category_id']) {
                $insertData[] = [
                    'event_id' => $event->id,
                    'element_id' => $mapping['element_id'],
                    'ticket_category_id' => $mapping['ticket_category_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($insertData)) {
            \App\Models\EventSeatingCategory::insert($insertData);
        }

        return back()->with('success', 'Sitzplatz-Kategorien gespeichert.');
    }

    public function generateAiDescription(Request $request)
    {
        if (!auth()->user()->hasRole('vendor')) abort(403);

        $validated = $request->validate([
            'title' => 'required|string',
            'tags' => 'nullable|string',
        ]);

        $title = $validated['title'];
        $tags = $validated['tags'] ?? 'keine';

        // Dummy AI implementation. In a real app, this would call OpenAI/Gemini API.
        $generated = "Erleben Sie '$title' hautnah! \n\nDieses besondere Event verspricht unvergessliche Momente. " . 
            "Egal, ob Sie alleine, mit Freunden oder der Familie kommen – es erwartet Sie ein perfekt abgestimmtes Programm " . 
            "und eine großartige Atmosphäre. Sichern Sie sich jetzt Ihre Tickets und seien Sie dabei, wenn es heißt: Vorhang auf für $title!\n\n" . 
            "Highlights: $tags";

        // Simulate API delay
        sleep(1);

        return response()->json([
            'description' => $generated
        ]);
    }
}
