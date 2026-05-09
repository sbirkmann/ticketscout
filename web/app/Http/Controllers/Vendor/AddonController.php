<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Addon;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    private function authorizeEvent(Event $event)
    {
        if ($event->vendor_id !== auth()->id()) abort(403);
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'nullable|integer|min:1',
            'ticket_categories' => 'nullable|array',
            'ticket_categories.*' => 'exists:ticket_categories,id',
        ]);

        $addon = $event->addons()->create(\Illuminate\Support\Arr::except($validated, ['ticket_categories']));

        if (isset($validated['ticket_categories'])) {
            $addon->ticketCategories()->sync($validated['ticket_categories']);
        }

        return back()->with('success', 'Add-on erstellt.');
    }

    public function update(Request $request, Event $event, Addon $addon)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'nullable|integer|min:1',
            'ticket_categories' => 'nullable|array',
            'ticket_categories.*' => 'exists:ticket_categories,id',
        ]);

        $addon->update(\Illuminate\Support\Arr::except($validated, ['ticket_categories']));

        if (isset($validated['ticket_categories'])) {
            $addon->ticketCategories()->sync($validated['ticket_categories']);
        } else {
            $addon->ticketCategories()->sync([]);
        }

        return back()->with('success', 'Add-on aktualisiert.');
    }

    public function destroy(Event $event, Addon $addon)
    {
        $this->authorizeEvent($event);
        $addon->ticketCategories()->detach();
        $addon->delete();
        return back()->with('success', 'Add-on gelöscht.');
    }
}
