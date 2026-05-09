<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;
use App\Models\Ticket;

class CheckinController extends Controller
{
    public function index()
    {
        $events = Event::where('vendor_id', auth()->user()->vendor_id)
            ->whereDate('start_date', '>=', now()->subDays(2))
            ->whereDate('start_date', '<=', now()->addDays(7))
            ->orderBy('start_date', 'asc')
            ->get();

        $stats = [];
        foreach ($events as $event) {
            $totalTickets = Ticket::whereHas('order', function($q) use ($event) {
                $q->where('event_id', $event->id);
            })->where('status', 'valid')->count();

            $scannedTickets = Ticket::whereHas('order', function($q) use ($event) {
                $q->where('event_id', $event->id);
            })->where('status', 'valid')->whereNotNull('scanned_at')->count();
            
            $stats[] = [
                'event' => $event,
                'total' => $totalTickets,
                'scanned' => $scannedTickets,
                'percentage' => $totalTickets > 0 ? round(($scannedTickets / $totalTickets) * 100) : 0
            ];
        }

        return Inertia::render('Vendor/Checkin/Index', [
            'stats' => $stats
        ]);
    }
}
