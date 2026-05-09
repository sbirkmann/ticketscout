<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SeatingPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeatingPlanController extends Controller
{
    public function index()
    {
        $seatingPlans = SeatingPlan::with('location')->orderBy('name')->get();
        $locations = \App\Models\Location::orderBy('name')->get();

        return Inertia::render('Superadmin/SeatingPlans/Index', [
            'seatingPlans' => $seatingPlans,
            'locations' => $locations
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'layout_data' => 'nullable|array',
            'bg_image' => 'nullable|image|max:5120',
        ]);

        $data = \Illuminate\Support\Arr::except($validated, ['bg_image']);

        if ($request->hasFile('bg_image')) {
            $data['bg_image_path'] = $request->file('bg_image')->store('seating_plans', 'public');
        }

        SeatingPlan::create($data);

        return back()->with('success', 'Saalplan erfolgreich erstellt.');
    }

    public function update(Request $request, SeatingPlan $seatingPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'layout_data' => 'nullable|array',
            'bg_image' => 'nullable|image|max:5120',
        ]);

        $data = \Illuminate\Support\Arr::except($validated, ['bg_image']);

        if ($request->hasFile('bg_image')) {
            $data['bg_image_path'] = $request->file('bg_image')->store('seating_plans', 'public');
        }

        $seatingPlan->update($data);

        return back()->with('success', 'Saalplan aktualisiert.');
    }

    public function destroy(SeatingPlan $seatingPlan)
    {
        $seatingPlan->delete();
        return back()->with('success', 'Saalplan gelöscht.');
    }
}
