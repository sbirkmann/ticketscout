<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'vendor_id',
        'event_id',
        'code',
        'type',
        'value',
        'max_uses',
        'current_uses',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'value' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
    
    public function isValidForEvent($eventId = null)
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses && $this->current_uses >= $this->max_uses) return false;
        
        // If promo code is bound to a specific event, check it.
        if ($this->event_id && $this->event_id != $eventId) return false;
        
        return true;
    }
    
    public function calculateDiscount($total)
    {
        if ($this->type === 'percent') {
            return $total * ($this->value / 100);
        }
        return min($total, $this->value);
    }
}
