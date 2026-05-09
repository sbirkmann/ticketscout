<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Location;
use Illuminate\Support\Facades\Route;

class FrontendController extends Controller
{
    public function index()
    {
        $query = Event::where('status', 'published')
            ->where('is_approved', true)
            ->whereDate('start_date', '>=', now())
            ->with(['location', 'vendor', 'category'])
            ->orderBy('start_date', 'asc');

        $this->applyVendorContext($query);

        $events = $query->take(8)->get();

        $categories = EventCategory::all();

        $locations = Location::where('is_global', true)->where('is_approved', true)->take(4)->get();

        return Inertia::render('Welcome', [
            'events' => $events,
            'categories' => $categories,
            'locations' => $locations
        ]);
    }
    
    public function map()
    {
        // Load all future published events with locations
        $query = Event::where('status', 'published')
            ->where('is_approved', true)
            ->whereDate('start_date', '>=', now())
            ->whereNotNull('location_id')
            ->with(['location', 'category']);

        $this->applyVendorContext($query);

        $events = $query->get();
            
        // Map data for the frontend
        $mappedEvents = $events->map(function($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'start_date' => $event->start_date,
                'category' => $event->category->name ?? 'Event',
                'location' => [
                    'name' => $event->location->name,
                    'address' => $event->location->address,
                    'city' => $event->location->city,
                ],
                // We mock coordinates based on city string length just for visual demonstration
                // A real app would geocode the address
                'lat' => 51.165691 + (strlen($event->location->city) * 0.1) - (strlen($event->location->zip) * 0.05),
                'lng' => 10.451526 + (strlen($event->location->name) * 0.1) - (strlen($event->location->city) * 0.05),
            ];
        });

        return Inertia::render('Map', [
            'events' => $mappedEvents
        ]);
    }

    public function events(Request $request)
    {
        $query = Event::where('status', 'published')
            ->where('is_approved', true)
            ->with(['location', 'vendor', 'category', 'ticketCategories']);

        $this->applyVendorContext($query);

        // Filter by search term
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('event_category_id', $request->category);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        } else {
            // Default to future events if no explicit date range is given
            $query->whereDate('start_date', '>=', now());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', $request->date_to);
        }

        // Sort
        $sort = $request->input('sort', 'date_asc');
        switch ($sort) {
            case 'date_desc':
                $query->orderBy('start_date', 'desc');
                break;
            case 'date_asc':
            default:
                $query->orderBy('start_date', 'asc');
                break;
        }

        $events = $query->paginate(12)->withQueryString();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'filters' => $request->only(['search', 'category', 'date_from', 'date_to', 'sort']),
            'categories' => EventCategory::all(),
        ]);
    }

    private function applyVendorContext($query)
    {
        if (request()->has('vendor_context')) {
            $query->where('vendor_id', request()->vendor_context);
        }
    }

    public function locations()
    {
        $locations = Location::where('is_global', true)->where('is_approved', true)->get()->map(function($location) {
            $location->lat = 51.165691 + (strlen($location->city ?? '') * 0.1) - (strlen($location->zip ?? '') * 0.05);
            $location->lng = 10.451526 + (strlen($location->name ?? '') * 0.1) - (strlen($location->city ?? '') * 0.05);
            return $location;
        });

        return Inertia::render('Locations/Index', [
            'locations' => $locations
        ]);
    }

    public function artists()
    {
        $artists = \App\Models\Artist::where('is_published', true)->get();

        return Inertia::render('Artists/Index', [
            'artists' => $artists
        ]);
    }

    public function downloadIcs($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        
        $icsContent = "BEGIN:VCALENDAR\r\n";
        $icsContent .= "VERSION:2.0\r\n";
        $icsContent .= "PRODID:-//Ticketsout24//DE\r\n";
        $icsContent .= "BEGIN:VEVENT\r\n";
        $icsContent .= "UID:" . $event->id . "@ticketsout24.de\r\n";
        $icsContent .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $icsContent .= "DTSTART:" . gmdate('Ymd\THis\Z', strtotime($event->start_date)) . "\r\n";
        if ($event->end_date) {
            $icsContent .= "DTEND:" . gmdate('Ymd\THis\Z', strtotime($event->end_date)) . "\r\n";
        }
        $icsContent .= "SUMMARY:" . $event->title . "\r\n";
        $icsContent .= "DESCRIPTION:" . strip_tags($event->description) . "\r\n";
        if ($event->location) {
            $icsContent .= "LOCATION:" . $event->location->name . ", " . $event->location->address . ", " . $event->location->zip . " " . $event->location->city . "\r\n";
        }
        $icsContent .= "URL:" . route('event.show', $event->slug) . "\r\n";
        $icsContent .= "END:VEVENT\r\n";
        $icsContent .= "END:VCALENDAR\r\n";

        return response($icsContent)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="event-' . $event->slug . '.ics"');
    }

    public function showEvent($slug)
    {
        $event = Event::where('slug', $slug)
            ->where('status', 'published')
            ->where('is_approved', true)
            ->with(['location', 'vendor', 'ticketCategories' => function ($query) {
                $query->where('is_active', true);
            }, 'addons.ticketCategories', 'category', 'artists', 'siblingDates' => function($q) {
                $q->where('status', 'published')->where('is_approved', true);
            }, 'parentEvent' => function($q) {
                $q->where('status', 'published')->where('is_approved', true)->with('siblingDates');
            }])
            ->firstOrFail();

        // Affiliate Link Tracking
        if (request()->has('ref')) {
            $affiliateLink = \App\Models\AffiliateLink::where('code', request('ref'))->first();
            if ($affiliateLink) {
                // Increment clicks (only once per session)
                if (!session()->has('tracked_ref_' . $affiliateLink->code)) {
                    $affiliateLink->increment('clicks');
                    session()->put('tracked_ref_' . $affiliateLink->code, true);
                }
                
                // Store ref in session for order attribution (expires after 24 hours via default session lifetime, or we can use cookie)
                session()->put('affiliate_ref', $affiliateLink->id);
            }
        }

        // Calculate Social Proof / FOMO
        $totalCapacity = $event->ticketCategories->sum('capacity');
        $totalSold = \App\Models\Ticket::whereHas('order', function($q) use ($event) {
            $q->where('event_id', $event->id);
        })->where('status', 'valid')->count();
        $soldOutPercentage = $totalCapacity > 0 ? round(($totalSold / $totalCapacity) * 100) : 0;
        
        // Only show if more than 30% sold out to create genuine FOMO
        $showSoldOutBadge = $soldOutPercentage >= 30;

        // Simulated live viewers (cache for 5 minutes per event to keep it consistent for the user)
        $viewingNow = cache()->remember('event_viewers_' . $event->id, 300, function () {
            return rand(3, 18);
        });

        // Map dynamic prices for the frontend
        $event->ticketCategories->transform(function ($category) {
            // Override the price attribute directly so we don't have to change the frontend
            $category->price = $category->current_price;
            return $category;
        });

        return Inertia::render('Event/Show', [
            'event' => $event,
            'socialProof' => [
                'viewing_now' => $viewingNow,
                'sold_out_percentage' => $soldOutPercentage,
                'show_sold_out_badge' => $showSoldOutBadge
            ]
        ]);
    }

    public function showArtist($slug)
    {
        $artist = \App\Models\Artist::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        if (!$artist->has_landing_page) {
            abort(404);
        }

        $events = $artist->events()
            ->where('status', 'published')
            ->where('is_approved', true)
            ->whereDate('start_date', '>=', now())
            ->with(['location', 'category'])
            ->orderBy('start_date', 'asc')
            ->get();

        return Inertia::render('Artists/Show', [
            'artist' => $artist,
            'events' => $events
        ]);
    }

    public function showLocation($slug)
    {
        $location = Location::where('slug', $slug)->where('is_approved', true)->firstOrFail();
        $events = Event::where('location_id', $location->id)
            ->where('status', 'published')
            ->where('is_approved', true)
            ->whereDate('start_date', '>=', now())
            ->with(['category', 'ticketCategories'])
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(function ($event) {
                $minPrice = $event->ticketCategories->min('price');
                $event->min_price = $minPrice;
                return $event;
            });

        return Inertia::render('Locations/Show', [
            'location' => $location,
            'events'   => $events
        ]);
    }

    public function showCategory($slug)
    {
        $category = EventCategory::where('slug', $slug)->firstOrFail();
        $events = Event::where('event_category_id', $category->id)
            ->where('status', 'published')
            ->where('is_approved', true)
            ->whereDate('start_date', '>=', now())
            ->with(['location', 'vendor', 'ticketCategories'])
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(function ($event) {
                $event->min_price = $event->ticketCategories->min('price');
                return $event;
            });

        return Inertia::render('Categories/Show', [
            'category' => $category,
            'events' => $events
        ]);
    }

    public function cities()
    {
        $cities = \App\Models\City::orderBy('name', 'asc')->get();
        return Inertia::render('Cities/Index', [
            'cities' => $cities
        ]);
    }

    public function showCity($slug)
    {
        $city = \App\Models\City::where('slug', $slug)->firstOrFail();
        
        $locations = Location::where('city', $city->name)->where('is_approved', true)->get();
        $locationIds = $locations->pluck('id');
        
        $events = Event::whereIn('location_id', $locationIds)
            ->where('status', 'published')
            ->where('is_approved', true)
            ->whereDate('start_date', '>=', now())
            ->with(['location', 'category', 'ticketCategories'])
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(function ($event) {
                $event->min_price = $event->ticketCategories->min('price');
                return $event;
            });

        return Inertia::render('Cities/Show', [
            'city' => $city,
            'locations' => $locations,
            'events' => $events
        ]);
    }
}
