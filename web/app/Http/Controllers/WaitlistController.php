<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function store(Request $request, \App\Models\Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $event->waitlists()->create($validated);

        return back()->with('success', 'Du wurdest erfolgreich in die Warteliste eingetragen.');
    }
}
