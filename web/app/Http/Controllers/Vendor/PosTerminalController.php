<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PosTerminal;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class PosTerminalController extends Controller
{
    public function index()
    {
        $terminals = PosTerminal::where('vendor_id', auth()->id())->get();

        return Inertia::render('Vendor/PosTerminals/Index', [
            'terminals' => $terminals
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pin' => 'required|string|min:4|max:10',
            'printer_type' => 'nullable|string',
            'printer_ip' => 'nullable|string',
            'terminal_type' => 'required|string|in:web,stripe_terminal,zvt'
        ]);

        // Generate a unique 6 digit alphanumeric login code
        $loginCode = strtoupper(Str::random(6));
        while(PosTerminal::where('login_code', $loginCode)->exists()) {
            $loginCode = strtoupper(Str::random(6));
        }

        PosTerminal::create([
            'vendor_id' => auth()->id(),
            'name' => $validated['name'],
            'login_code' => $loginCode,
            'pin' => Hash::make($validated['pin']),
            'is_active' => true,
            'printer_type' => $validated['printer_type'] ?? null,
            'printer_ip' => $validated['printer_ip'] ?? null,
            'terminal_type' => $validated['terminal_type'] ?? 'web'
        ]);

        return back()->with('success', 'POS-Kasse erstellt. Der Login-Code lautet: ' . $loginCode);
    }

    public function destroy(PosTerminal $pos_terminal)
    {
        if ($pos_terminal->vendor_id !== auth()->id()) abort(403);
        $pos_terminal->delete();
        return back()->with('success', 'POS-Kasse gelöscht.');
    }
}
