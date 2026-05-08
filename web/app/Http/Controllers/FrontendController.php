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
        $events = Event::where('status', 'published')
            ->where('is_approved', true)
            ->whereDate('start_date', '>=', now())
            ->with(['location', 'vendor', 'category'])
            ->orderBy('start_date', 'asc')
            ->take(8)
            ->get();

        $categories = EventCategory::all();

        $locations = Location::where('is_global', true)->where('is_approved', true)->take(4)->get();

        return Inertia::render('Welcome', [
            'events' => $events,
            'categories' => $categories,
            'locations' => $locations
        ]);
    }

    public function locations()
    {
        $locations = Location::where('is_global', true)->where('is_approved', true)->get();

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

    public function showEvent($slug)
    {
        $event = Event::where('slug', $slug)
            ->where('status', 'published')
            ->where('is_approved', true)
            ->with(['location', 'vendor', 'ticketCategories' => function ($query) {
                $query->where('is_active', true);
            }, 'addons', 'category', 'artists', 'siblingDates' => function($q) {
                $q->where('status', 'published')->where('is_approved', true);
            }, 'parentEvent' => function($q) {
                $q->where('status', 'published')->where('is_approved', true)->with('siblingDates');
            }])
            ->firstOrFail();

        return Inertia::render('Event/Show', [
            'event' => $event
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
}
