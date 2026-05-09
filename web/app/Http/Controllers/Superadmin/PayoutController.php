<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Payout;
use App\Models\User;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::with('vendor')->latest()->paginate(20);
        $vendors = User::role('vendor')->get();

        return Inertia::render('Admin/Payouts/Index', [
            'payouts' => $payouts,
            'vendors' => $vendors
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|in:pending,paid',
            'reference' => 'nullable|string|max:255',
        ]);

        if ($validated['status'] === 'paid') {
            $validated['paid_at'] = now();
        }

        Payout::create($validated);

        return back()->with('success', 'Auszahlung erfolgreich angelegt.');
    }

    public function update(Request $request, Payout $payout)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid',
            'reference' => 'nullable|string|max:255',
        ]);

        if ($validated['status'] === 'paid' && $payout->status !== 'paid') {
            $validated['paid_at'] = now();
        } elseif ($validated['status'] === 'pending') {
            $validated['paid_at'] = null;
        }

        $payout->update($validated);

        return back()->with('success', 'Auszahlung aktualisiert.');
    }
}
