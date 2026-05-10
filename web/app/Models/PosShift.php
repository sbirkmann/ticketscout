<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosShift extends Model
{
    protected $guarded = [];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function cashTransactions()
    {
        return $this->hasMany(PosCashTransaction::class, 'pos_shift_id');
    }

    public function receipts()
    {
        return $this->hasMany(PosReceipt::class, 'pos_shift_id');
    }
}
