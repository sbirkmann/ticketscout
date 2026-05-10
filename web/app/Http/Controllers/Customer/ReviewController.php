<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if user has already reviewed
        $existingReview = Review::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingReview) {
            return back()->with('error', 'Du hast dieses Event bereits bewertet.');
        }

        // Check if user actually bought a ticket (optional, but good for marketplace)
        $hasOrdered = $event->orders()->where('user_id', auth()->id())->where('status', 'paid')->exists();
        
        if (!$hasOrdered) {
            return back()->with('error', 'Du kannst nur Events bewerten, für die du ein Ticket gekauft hast.');
        }

        Review::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_approved' => true, // default to true, can be moderated later
        ]);

        return back()->with('success', 'Vielen Dank für deine Bewertung!');
    }
}
