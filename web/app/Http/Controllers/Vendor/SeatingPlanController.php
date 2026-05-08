<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\SeatingPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class SeatingPlanController extends Controller
{
    public function index(Location $location)
    {
        if ($location->vendor_id !== auth()->id() && !$location->is_global) {
            abort(403);
        }

        return Inertia::render('Vendor/SeatingPlans/Index', [
            'location' => $location,
            'seatingPlans' => $location->seatingPlans,
        ]);
    }

    public function create(Location $location)
    {
        if ($location->vendor_id !== auth()->id() && !$location->is_global) {
            abort(403);
        }

        return Inertia::render('Vendor/SeatingPlans/Builder', [
            'location' => $location,
            'seatingPlan' => null,
        ]);
    }

    public function store(Request $request, Location $location)
    {
        if ($location->vendor_id !== auth()->id() && !$location->is_global) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'layout_data' => 'nullable|array',
            'bg_image' => 'nullable|image|max:2048',
        ]);

        $seatingPlan = new SeatingPlan();
        $seatingPlan->location_id = $location->id;
        $seatingPlan->name = $validated['name'];
        $seatingPlan->layout_data = $validated['layout_data'] ?? ['elements' => []];

        if ($request->hasFile('bg_image')) {
            $seatingPlan->bg_image_path = $request->file('bg_image')->store('seating_plans', 'public');
        }

        $seatingPlan->save();

        return redirect()->route('vendor.locations.seating-plans.index', $location->id)->with('success', 'Saalplan gespeichert.');
    }

    public function edit(Location $location, SeatingPlan $seatingPlan)
    {
        if ($location->vendor_id !== auth()->id() && !$location->is_global) abort(403);
        if ($seatingPlan->location_id !== $location->id) abort(404);

        return Inertia::render('Vendor/SeatingPlans/Builder', [
            'location' => $location,
            'seatingPlan' => $seatingPlan,
        ]);
    }

    public function update(Request $request, Location $location, SeatingPlan $seatingPlan)
    {
        if ($location->vendor_id !== auth()->id() && !$location->is_global) abort(403);
        if ($seatingPlan->location_id !== $location->id) abort(404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'layout_data' => 'nullable|array',
            'bg_image' => 'nullable|image|max:2048',
        ]);

        $seatingPlan->name = $validated['name'];
        $seatingPlan->layout_data = $validated['layout_data'] ?? ['elements' => []];

        if ($request->hasFile('bg_image')) {
            $seatingPlan->bg_image_path = $request->file('bg_image')->store('seating_plans', 'public');
        }

        $seatingPlan->save();

        return redirect()->route('vendor.locations.seating-plans.index', $location->id)->with('success', 'Saalplan aktualisiert.');
    }

    public function destroy(Location $location, SeatingPlan $seatingPlan)
    {
        if ($location->vendor_id !== auth()->id() && !$location->is_global) abort(403);
        if ($seatingPlan->location_id !== $location->id) abort(404);

        $seatingPlan->delete();
        return redirect()->route('vendor.locations.seating-plans.index', $location->id)->with('success', 'Saalplan gelöscht.');
    }
}
