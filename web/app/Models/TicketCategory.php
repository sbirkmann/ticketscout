<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    protected $guarded = [];

    public function addons()
    {
        return $this->belongsToMany(Addon::class);
    }
}
