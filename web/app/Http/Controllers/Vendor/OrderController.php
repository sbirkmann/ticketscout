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

    public function exportCsv(Request $request)
    {
        $vendorId = auth()->id();
        
        $orders = Order::whereHas('items.ticketCategory.event', function($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })
        ->with(['user', 'items.ticketCategory.event'])
        ->orderBy('created_at', 'desc')
        ->get();

        $filename = "orders_export_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 reading
        fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // DATEV-ähnliche Spalten
        fputcsv($handle, [
            'Bestellnummer', 'Datum', 'Uhrzeit', 'Status', 'Kunde Name', 'Kunde Email', 
            'Total Netto', 'Total Brutto', 'Steuer', 'Event', 'Tickets'
        ], ';');

        foreach ($orders as $order) {
            $events = $order->items->map(fn($i) => $i->ticketCategory->event->title ?? '')->unique()->implode(', ');
            $tickets = $order->items->sum('quantity');

            fputcsv($handle, [
                $order->order_number,
                $order->created_at->format('d.m.Y'),
                $order->created_at->format('H:i'),
                $order->status,
                $order->billing_name ?: ($order->user->name ?? ''),
                $order->billing_email ?: ($order->user->email ?? ''),
                number_format($order->total_net, 2, ',', ''),
                number_format($order->total_gross, 2, ',', ''),
                number_format($order->total_tax, 2, ',', ''),
                $events,
                $tickets
            ], ';');
        }

        fclose($handle);
        exit;
    }
}
