<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\SeatingSeat;
use App\Models\GroupReservation;
use App\Models\TicketListing;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResaleController extends Controller
{
    // ── Fan-to-Fan Resale ────────────────────────────────────
    public function index()
    {
        $listings = TicketListing::where('status', 'active')
            ->with(['ticket.order.event', 'seller'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Resale/Index', [
            'listings' => $listings,
        ]);
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        $tickets = $user->orders()
            ->with(['tickets.event', 'event'])
            ->whereHas('tickets', fn($q) => $q->where('status', 'valid'))
            ->get()
            ->flatMap(fn($order) => $order->tickets)
            ->filter(fn($ticket) => $ticket->status === 'valid');

        return Inertia::render('Resale/Create', [
            'tickets' => $tickets->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'asking_price' => 'required|numeric|min:0.01',
        ]);

        $ticket = \App\Models\Ticket::findOrFail($validated['ticket_id']);
        if ($ticket->order->user_id !== auth()->id()) {
            abort(403);
        }

        // Prevent double-listing
        if (TicketListing::where('ticket_id', $ticket->id)->where('status', 'active')->exists()) {
            return back()->withErrors(['ticket_id' => 'Dieses Ticket ist bereits zum Verkauf angeboten.']);
        }

        TicketListing::create([
            'ticket_id' => $ticket->id,
            'seller_id' => auth()->id(),
            'asking_price' => $validated['asking_price'],
            'status' => 'active',
        ]);

        return back()->with('success', 'Ticket erfolgreich zum Verkauf angeboten.');
    }

    public function cancel(TicketListing $listing)
    {
        if ($listing->seller_id !== auth()->id()) abort(403);
        $listing->update(['status' => 'cancelled']);
        return back()->with('success', 'Angebot zurückgezogen.');
    }

    // ── Group Reservations ────────────────────────────────────
    public function createGroup(Request $request, Event $event)
    {
        return Inertia::render('Group/Create', [
            'event' => $event->load(['ticketCategories', 'location']),
        ]);
    }

    public function storeGroup(Request $request, Event $event)
    {
        $validated = $request->validate([
            'total_tickets' => 'required|integer|min:2|max:20',
            'ticket_category' => 'required|string',
            'price_per_ticket' => 'required|numeric|min:0',
        ]);

        $reservation = GroupReservation::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'share_token' => GroupReservation::generateToken(),
            'total_tickets' => $validated['total_tickets'],
            'ticket_category' => $validated['ticket_category'],
            'price_per_ticket' => $validated['price_per_ticket'],
            'status' => 'open',
            'participants' => [],
            'expires_at' => now()->addDays(3),
        ]);

        return redirect()->route('group.show', $reservation->share_token)
            ->with('success', 'Gruppenreservierung erstellt! Teile den Link mit deinen Freunden.');
    }

    public function showGroup($token)
    {
        $reservation = GroupReservation::where('share_token', $token)
            ->with(['event.location'])
            ->firstOrFail();

        return Inertia::render('Group/Show', [
            'reservation' => $reservation,
            'shareUrl' => url('/gruppe/' . $token),
        ]);
    }
}
