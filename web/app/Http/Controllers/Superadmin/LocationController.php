<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::orderBy('name')->get();

        return Inertia::render('Superadmin/Locations/Index', [
            'locations' => $locations
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string',
            'zip' => 'required|string',
            'country' => 'required|string',
            'description' => 'nullable|string',
            'is_global' => 'boolean',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);
        $i = 1;
        while (Location::where('slug', $slug)->exists()) {
            $slug = \Illuminate\Support\Str::slug($validated['name']) . '-' . $i++;
        }

        Location::create(array_merge($validated, ['slug' => $slug]));

        return back()->with('success', 'Location erfolgreich erstellt.');
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string',
            'zip' => 'required|string',
            'country' => 'required|string',
            'description' => 'nullable|string',
            'is_global' => 'boolean',
        ]);

        $location->update($validated);

        return back()->with('success', 'Location aktualisiert.');
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return back()->with('success', 'Location gelöscht.');
    }

    public function toggleGlobal(Location $location)
    {
        $location->update(['is_global' => !$location->is_global]);

        return back()->with('success', $location->is_global
            ? 'Location ist jetzt global sichtbar.'
            : 'Location ist nicht mehr global sichtbar.'
        );
    }
}
