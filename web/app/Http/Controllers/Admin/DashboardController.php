<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingEvents = Event::where('is_approved', false)
            ->with(['vendor', 'location', 'category'])
            ->get();

        $pendingLocations = Location::where('is_approved', false)
            ->with('vendor')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'pendingEvents' => $pendingEvents,
            'pendingLocations' => $pendingLocations,
        ]);
    }

    public function approveEvent(Event $event)
    {
        $event->update(['is_approved' => true]);
        return back()->with('success', 'Event freigegeben.');
    }

    public function approveLocation(Location $location)
    {
        $location->update(['is_approved' => true]);
        return back()->with('success', 'Location freigegeben.');
    }
}
