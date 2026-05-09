<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketListing extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
