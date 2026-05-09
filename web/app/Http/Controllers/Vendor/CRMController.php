<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;
use App\Models\Order;
use App\Mail\AttendeeMessage;
use Illuminate\Support\Facades\Mail;

class CRMController extends Controller
{
    public function index()
    {
        $events = Event::where('vendor_id', auth()->user()->vendor_id)
            ->withCount('orders')
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('Vendor/CRM/Index', [
            'events' => $events
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $event = Event::where('id', $validated['event_id'])
            ->where('vendor_id', auth()->user()->vendor_id)
            ->firstOrFail();

        // Get unique email addresses from paid orders
        $emails = Order::where('event_id', $event->id)
            ->where('status', 'paid')
            ->whereNotNull('billing_email')
            ->pluck('billing_email')
            ->unique();

        if ($emails->isEmpty()) {
            return back()->with('error', 'Keine Ticketkäufer für dieses Event gefunden.');
        }

        foreach ($emails as $email) {
            Mail::to($email)->queue(new AttendeeMessage($event, $validated['subject'], $validated['message']));
        }

        return redirect()->route('vendor.crm.index')
            ->with('success', 'Die Nachricht wurde an ' . $emails->count() . ' Empfänger gesendet!');
    }
}
