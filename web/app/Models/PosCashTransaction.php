<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosCashTransaction extends Model
{
    protected $guarded = [];

    public function shift()
    {
        return $this->belongsTo(PosShift::class, 'pos_shift_id');
    }
}
