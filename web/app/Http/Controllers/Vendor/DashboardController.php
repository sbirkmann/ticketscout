<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Ensure vendor has Stripe connected (placeholder logic for UI state)
        $stripeConnected = $user->vendorSettings()->whereNotNull('stripe_account_id')->exists();

        // Get vendor's events
        $events = $user->events()->with('category')->latest()->take(5)->get();
        $eventIds = $user->events()->pluck('id');

        // Get orders for those events
        $orders = Order::whereIn('event_id', $eventIds)->latest()->take(5)->with(['event'])->get();

        // Stats
        $totalRevenue = Order::whereIn('event_id', $eventIds)->where('status', 'paid')->sum('total_amount');
        $totalOrders = Order::whereIn('event_id', $eventIds)->where('status', 'paid')->count();
        $activeEvents = $user->events()->where('status', 'published')->count();

        return Inertia::render('Vendor/Dashboard', [
            'stripeConnected' => $stripeConnected,
            'recentEvents'    => $events,
            'recentOrders'    => $orders,
            'stats'           => [
                'revenue' => $totalRevenue,
                'orders'  => $totalOrders,
                'events'  => $activeEvents,
            ]
        ]);
    }
}
