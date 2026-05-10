<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Load past orders with tickets and events
        $orders = Order::where('user_id', $user->id)
            ->with(['event.location', 'event.category', 'items.ticketCategory', 'tickets'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Collect bought event categories and tags for recommendations
        $boughtCategoryIds = [];
        $boughtTags = [];
        foreach ($orders as $order) {
            if ($order->event->event_category_id) {
                $boughtCategoryIds[] = $order->event->event_category_id;
            }
            if ($order->event->tags) {
                $tagsArray = is_string($order->event->tags) ? json_decode($order->event->tags, true) : $order->event->tags;
                if (is_array($tagsArray)) {
                    $boughtTags = array_merge($boughtTags, $tagsArray);
                }
            }
        }
        $boughtCategoryIds = array_unique($boughtCategoryIds);
        $boughtTags = array_unique($boughtTags);

        // Get recommendations based on bought categories or tags
        $recommendationsQuery = Event::where('status', 'published')
            ->with(['location', 'vendor', 'category'])
            ->whereDate('start_date', '>=', now());

        if (!empty($boughtCategoryIds) || !empty($boughtTags)) {
            $recommendationsQuery->where(function($q) use ($boughtCategoryIds, $boughtTags) {
                if (!empty($boughtCategoryIds)) {
                    $q->orWhereIn('event_category_id', $boughtCategoryIds);
                }
                if (!empty($boughtTags)) {
                    foreach ($boughtTags as $tag) {
                        $q->orWhereJsonContains('tags', $tag);
                    }
                }
            });
        }

        $recommendations = $recommendationsQuery->take(4)->get();

        // If not enough recommendations from same categories, fill up with other upcoming events
        if ($recommendations->count() < 4) {
            $moreRecommendations = Event::where('status', 'published')
                ->with(['location', 'vendor', 'category'])
                ->whereDate('start_date', '>=', now())
                ->whereNotIn('id', $recommendations->pluck('id'))
                ->take(4 - $recommendations->count())
                ->get();
            
            $recommendations = $recommendations->merge($moreRecommendations);
        }

        // Map orders for the frontend
        $mappedOrders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'is_gift' => $order->is_gift,
                'gift_recipient_name' => $order->gift_recipient_name,
                'gift_message' => $order->gift_message,
                'event' => [
                    'id' => $order->event->id,
                    'title' => $order->event->title,
                    'slug' => $order->event->slug,
                    'start_date' => $order->event->start_date,
                    'image_path' => $order->event->image_path,
                    'location' => $order->event->location ? $order->event->location->name : 'N/A',
                ],
                'tickets_count' => $order->tickets->count(),
                'tickets' => $order->tickets->map(function ($ticket) {
                    return [
                        'id' => $ticket->id,
                        'qr_code_hash' => $ticket->qr_code_hash,
                        'status' => $ticket->status,
                        'category' => $ticket->category ? $ticket->category->name : 'Standard',
                    ];
                })
            ];
        });

        return Inertia::render('Customer/Dashboard', [
            'orders' => $mappedOrders,
            'recommendations' => $recommendations
        ]);
    }

    public function downloadTickets(Order $order, \App\Services\TicketService $ticketService)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        
        $order->load(['tickets.ticketCategory.event.vendor', 'tickets.order']);
        
        $pdfContent = $ticketService->generatePdfForOrder($order);
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="tickets-' . $order->id . '.pdf"');
    }

    public function downloadInvoice(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $invoice = \App\Models\Invoice::where('order_id', $order->id)
            ->where('type', 'vendor_to_customer')
            ->first();

        if (!$invoice || !$invoice->pdf_path) {
            // Generate it on the fly if it somehow doesn't exist
            $invoiceService = new \App\Services\InvoiceService();
            $invoice = $invoiceService->generateVendorToCustomerInvoice($order);
        }

        $path = storage_path('app/' . $invoice->pdf_path);
        
        if (!file_exists($path)) {
            abort(404, 'Invoice not found.');
        }

        return response()->download($path, 'Rechnung-' . $invoice->invoice_number . '.pdf');
    }
}
