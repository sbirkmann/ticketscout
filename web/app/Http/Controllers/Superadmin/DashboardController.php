<?php

namespace App\Http\Controllers\Superadmin;

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
            ->with(['vendor', 'location'])
            ->latest()
            ->get();

        $pendingLocations = Location::where('is_approved', false)
            ->with('vendor')
            ->latest()
            ->get();

        $stats = [
            'total_revenue' => \App\Models\Order::where('payment_status', 'paid')->sum('total_amount'),
            'total_platform_fees' => \App\Models\Order::where('payment_status', 'paid')->sum('platform_fee'),
            'total_orders' => \App\Models\Order::where('payment_status', 'paid')->count(),
            'total_tickets_sold' => \App\Models\Ticket::count(),
            'active_events' => Event::where('status', 'published')->where('is_approved', true)->count(),
            'active_vendors' => \App\Models\User::role('vendor')->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'pendingEvents' => $pendingEvents,
            'pendingLocations' => $pendingLocations,
            'stats' => $stats,
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
