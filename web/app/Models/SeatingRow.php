<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatingRow extends Model
{
    protected $guarded = [];

    public function seatingPlan()
    {
        return $this->belongsTo(SeatingPlan::class);
    }

    public function seats()
    {
        return $this->hasMany(SeatingSeat::class)->orderBy('seat_number');
    }
}
