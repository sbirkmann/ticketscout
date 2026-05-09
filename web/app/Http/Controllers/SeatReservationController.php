<?php

namespace App\Http\Controllers;

use App\Models\SeatingSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeatReservationController extends Controller
{
    /**
     * Temporarily reserve seats for 5 minutes.
     * Frontend calls this when user selects seats.
     */
    public function reserve(Request $request)
    {
        $validated = $request->validate([
            'seat_ids'   => 'required|array|min:1|max:20',
            'seat_ids.*' => 'integer|exists:seating_seats,id',
            'token'      => 'nullable|string|max:64',
        ]);

        $token     = $validated['token'] ?? Str::random(32);
        $expiresAt = now()->addMinutes(5);

        // Release any previously held seats for this token
        if ($validated['token'] ?? null) {
            SeatingSeat::where('reservation_token', $token)
                ->where('status', 'reserved')
                ->update(['status' => 'available', 'reservation_token' => null, 'reserved_until' => null]);
        }

        // Also release any globally expired reservations (cleanup on every request)
        SeatingSeat::where('status', 'reserved')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<', now())
            ->update(['status' => 'available', 'reservation_token' => null, 'reserved_until' => null]);

        // Try to reserve the requested seats
        $seats = SeatingSeat::whereIn('id', $validated['seat_ids'])
            ->where(function ($q) use ($token) {
                $q->where('status', 'available')
                  ->orWhere(fn($q2) => $q2->where('status', 'reserved')->where('reservation_token', $token));
            })
            ->get();

        if ($seats->count() !== count($validated['seat_ids'])) {
            $alreadyTaken = array_diff($validated['seat_ids'], $seats->pluck('id')->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Einige Plätze sind bereits vergeben.',
                'taken_ids' => $alreadyTaken,
            ], 409);
        }

        SeatingSeat::whereIn('id', $seats->pluck('id'))
            ->update([
                'status'            => 'reserved',
                'reservation_token' => $token,
                'reserved_until'    => $expiresAt,
            ]);

        return response()->json([
            'success'    => true,
            'token'      => $token,
            'expires_at' => $expiresAt->toISOString(),
        ]);
    }

    /**
     * Release seats held by a token (user deselects or navigates away).
     */
    public function release(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|max:64',
        ]);

        SeatingSeat::where('reservation_token', $validated['token'])
            ->where('status', 'reserved')
            ->update(['status' => 'available', 'reservation_token' => null, 'reserved_until' => null]);

        return response()->json(['success' => true]);
    }
}
