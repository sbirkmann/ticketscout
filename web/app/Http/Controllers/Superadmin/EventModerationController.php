<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Inertia\Inertia;

class EventModerationController extends Controller
{
    public function index()
    {
        $events = Event::with(['vendor', 'location'])
            ->where('is_approved', false)
            ->where('status', '!=', 'draft') // Assuming vendors submit by setting status to published but is_approved is false
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/EventModeration/Index', [
            'events' => $events
        ]);
    }

    public function approve(Event $event)
    {
        $event->update(['is_approved' => true]);
        
        // Optionally send email to vendor
        
        return back()->with('success', 'Event wurde freigegeben.');
    }

    public function reject(Request $request, Event $event)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $event->update([
            'status' => 'draft',
            'is_approved' => false
        ]);

        // Optionally send email to vendor with reason

        return back()->with('success', 'Event wurde abgelehnt und zurück auf Entwurf gesetzt.');
    }
}
