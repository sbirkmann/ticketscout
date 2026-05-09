<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $guarded = [];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketCategories()
    {
        return $this->belongsToMany(TicketCategory::class);
    }
}
