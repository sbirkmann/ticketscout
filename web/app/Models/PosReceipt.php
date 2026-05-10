<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosReceipt extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tax_details' => 'array',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function shift()
    {
        return $this->belongsTo(PosShift::class, 'pos_shift_id');
    }

    public function items()
    {
        return $this->hasMany(PosReceiptItem::class);
    }
}
