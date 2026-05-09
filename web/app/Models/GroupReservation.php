<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GroupReservation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'participants' => 'array',
        'expires_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateToken(): string
    {
        return Str::random(32);
    }

    public function getRemainingSpots(): int
    {
        $paid = collect($this->participants ?? [])->filter(fn($p) => !empty($p['paid_at']))->count();
        return $this->total_tickets - $paid;
    }
}
