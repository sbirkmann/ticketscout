<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    private function authorizeEvent(Event $event)
    {
        if ($event->vendor_id !== auth()->id()) abort(403);
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'price'                 => 'required|numeric|min:0',
            'quantity'              => 'nullable|integer|min:1',
            'is_default'            => 'boolean',
            'requires_attendee_name'=> 'boolean',
        ]);

        $event->ticketCategories()->create($validated);

        return back()->with('success', 'Ticketkategorie erstellt.');
    }

    public function update(Request $request, Event $event, TicketCategory $category)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'price'                 => 'required|numeric|min:0',
            'quantity'              => 'nullable|integer|min:1',
            'is_default'            => 'boolean',
            'requires_attendee_name'=> 'boolean',
        ]);

        $category->update($validated);

        return back()->with('success', 'Ticketkategorie aktualisiert.');
    }

    public function destroy(Event $event, TicketCategory $category)
    {
        $this->authorizeEvent($event);
        $category->delete();
        return back()->with('success', 'Ticketkategorie gelöscht.');
    }

    public function toggleActive(Event $event, TicketCategory $category)
    {
        $this->authorizeEvent($event);
        $category->update(['is_active' => !$category->is_active]);
        
        $status = $category->is_active ? 'freigegeben' : 'gesperrt';
        return back()->with('success', "Ticketkategorie {$status}.");
    }
}
