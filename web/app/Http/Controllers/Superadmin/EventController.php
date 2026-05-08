<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['vendor', 'location'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Superadmin/Events/Index', [
            'events' => $events
        ]);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return back()->with('success', 'Event gelöscht.');
    }
}
