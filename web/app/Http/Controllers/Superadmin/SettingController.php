<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = \App\Models\GlobalSetting::pluck('value', 'key')->toArray();
        return Inertia::render('Superadmin/Settings/Index', [
            'settings' => $settings
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'array',
            'settings.*' => 'nullable|string'
        ]);

        foreach ($validated['settings'] as $key => $value) {
            \App\Models\GlobalSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        cache()->forget('global_settings');

        return redirect()->back()->with('success', 'Einstellungen erfolgreich gespeichert.');
    }
}
