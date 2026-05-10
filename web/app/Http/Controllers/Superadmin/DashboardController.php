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

        $vendors = \App\Models\User::role('vendor')->select('id', 'name', 'email')->with('vendorSettings')->get();

        return Inertia::render('Admin/Dashboard', [
            'pendingEvents' => $pendingEvents,
            'pendingLocations' => $pendingLocations,
            'stats' => $stats,
            'vendors' => $vendors,
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

    public function updateVendorSettings(Request $request, \App\Models\User $user)
    {
        $validated = $request->validate([
            'custom_platform_fee' => 'nullable|numeric|min:0|max:100',
            'has_advanced_pos' => 'boolean'
        ]);

        $settings = $user->vendorSettings()->firstOrCreate([]);
        if (array_key_exists('custom_platform_fee', $validated)) {
            $settings->custom_platform_fee = $validated['custom_platform_fee'];
        }
        if (array_key_exists('has_advanced_pos', $validated)) {
            $settings->has_advanced_pos = $validated['has_advanced_pos'];
        }
        $settings->save();

        return back()->with('success', 'Veranstalter-Einstellungen aktualisiert.');
    }
}
