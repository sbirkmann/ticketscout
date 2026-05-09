<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatingSeat extends Model
{
    protected $guarded = [];

    public function seatingPlan()
    {
        return $this->belongsTo(SeatingPlan::class);
    }

    public function row()
    {
        return $this->belongsTo(SeatingRow::class, 'seating_row_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
