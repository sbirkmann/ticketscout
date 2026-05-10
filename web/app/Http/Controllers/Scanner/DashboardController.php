<?php

namespace App\Http\Controllers\Scanner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Scanner/Dashboard');
    }

    public function validateTicket(Request $request, $hash)
    {
        $ticket = \App\Models\Ticket::where('qr_hash', $hash)->with(['order.event'])->first();

        if (!$ticket) {
            return response()->json([
                'valid' => false,
                'message' => 'Ticket nicht gefunden. Möglicherweise ein gefälschter QR-Code.'
            ], 404);
        }

        // Check if event belongs to the vendor the scanner is assigned to
        // Assuming scanner user has vendor_id. If scanner is a user created by vendor, 
        // they should have a parent_id or vendor_id. For now, assuming $ticket->order->event->vendor_id == auth()->user()->parent_id
        if (auth()->user()->parent_id && $ticket->order->event->vendor_id !== auth()->user()->parent_id) {
            return response()->json([
                'valid' => false,
                'message' => 'Ticket gehört nicht zu einem eurer Events.'
            ], 403);
        }

        if ($ticket->status === 'scanned') {
            return response()->json([
                'valid' => false,
                'message' => 'Ticket wurde bereits gescannt am ' . $ticket->scanned_at->format('d.m.Y H:i') . '!',
                'ticket' => $ticket
            ]);
        }

        if ($ticket->status !== 'valid') {
            return response()->json([
                'valid' => false,
                'message' => 'Ticket ist storniert oder ungültig (Status: ' . $ticket->status . ').',
                'ticket' => $ticket
            ]);
        }

        // Mark as scanned
        $ticket->update([
            'status' => 'scanned',
            'scanned_at' => now(),
            'scanned_by' => auth()->id()
        ]);

        return response()->json([
            'valid' => true,
            'message' => 'Ticket erfolgreich entwertet!',
            'ticket' => $ticket
        ]);
    }
}
