<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\City;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::orderBy('name', 'asc')->paginate(20);
        
        return Inertia::render('Superadmin/Cities/Index', [
            'cities' => $cities
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('cities', 'public');
        }

        City::create($data);

        return redirect()->back()->with('success', 'Ort erfolgreich erstellt.');
    }

    public function update(Request $request, City $city)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('cities', 'public');
        }

        $city->update($data);

        return redirect()->back()->with('success', 'Ort erfolgreich aktualisiert.');
    }

    public function destroy(City $city)
    {
        $city->delete();
        return redirect()->back()->with('success', 'Ort gelöscht.');
    }
}
