<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatingPlan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'layout_data' => 'array',
    ];

    public function rows()
    {
        return $this->hasMany(SeatingRow::class)->orderBy('row_number');
    }

    public function seats()
    {
        return $this->hasMany(SeatingSeat::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
