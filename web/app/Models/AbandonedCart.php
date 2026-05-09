<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    protected $guarded = [];

    protected $casts = [
        'cart_data' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
