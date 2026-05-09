<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::where('vendor_id', auth()->user()->vendor_id)
            ->where('id', '!=', auth()->id())
            ->role('scanner')
            ->get();
            
        return Inertia::render('Vendor/Staff/Index', [
            'staff' => $staff
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'vendor_id' => auth()->user()->vendor_id,
        ]);

        $user->assignRole('scanner');

        return back()->with('success', 'Staff Account erfolgreich erstellt.');
    }

    public function destroy(User $staff)
    {
        if ($staff->vendor_id !== auth()->user()->vendor_id) {
            abort(403);
        }

        $staff->delete();
        
        return back()->with('success', 'Staff Account gelöscht.');
    }
}
