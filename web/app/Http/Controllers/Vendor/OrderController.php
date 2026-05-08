<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Event;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = auth()->id();
        
        $orders = Order::whereHas('items.ticketCategory', function ($query) use ($vendorId) {
            $query->whereHas('event', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            });
        })
        ->with(['user', 'items.ticketCategory.event'])
        ->latest()
        ->paginate(20);

        // Fetch basic stats
        $stats = [
            'total_orders' => Order::whereHas('items.ticketCategory.event', fn($q) => $q->where('vendor_id', $vendorId))->count(),
            'revenue' => Order::whereHas('items.ticketCategory.event', fn($q) => $q->where('vendor_id', $vendorId))->where('status', 'paid')->sum('total_gross'),
            'paid_orders' => Order::whereHas('items.ticketCategory.event', fn($q) => $q->where('vendor_id', $vendorId))->where('status', 'paid')->count(),
        ];

        return Inertia::render('Vendor/Orders/Index', [
            'orders' => $orders,
            'stats' => $stats
        ]);
    }

    public function show($id)
    {
        $vendorId = auth()->id();
        $order = Order::with(['user', 'items.ticketCategory.event', 'tickets.category', 'tickets.event'])
            ->whereHas('items.ticketCategory.event', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->findOrFail($id);

        return Inertia::render('Vendor/Orders/Show', [
            'order' => $order
        ]);
    }

    public function resendTickets($id)
    {
        $vendorId = auth()->id();
        $order = Order::whereHas('items.ticketCategory.event', function($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->findOrFail($id);

        // Hier würde die Mail-Logik getriggert werden.
        // Mail::to($order->billing_email)->send(new OrderTicketsMail($order));

        return back()->with('success', 'Tickets wurden erneut an ' . $order->billing_email . ' gesendet.');
    }
}
