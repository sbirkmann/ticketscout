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

    public function getCurrentPriceAttribute()
    {
        if (!$this->use_dynamic_pricing || !$this->capacity || !$this->dynamic_pricing_threshold_percent) {
            return $this->price;
        }

        $sold = $this->sold ?? 0;
        $remaining = max(0, $this->capacity - $sold);
        $percentRemaining = ($remaining / $this->capacity) * 100;

        if ($percentRemaining <= $this->dynamic_pricing_threshold_percent) {
            return $this->price + ($this->dynamic_pricing_increase_amount ?? 0);
        }

        return $this->price;
    }
}
