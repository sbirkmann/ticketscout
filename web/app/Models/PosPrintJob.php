<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosPrintJob extends Model
{
    protected $guarded = [];

    protected $casts = [
        'printed_at' => 'datetime',
    ];

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function receipt()
    {
        return $this->belongsTo(PosReceipt::class, 'pos_receipt_id');
    }
}
