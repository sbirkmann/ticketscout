<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = [];

    public function ticketCategory()
    {
        return $this->belongsTo(TicketCategory::class);
    }
}
