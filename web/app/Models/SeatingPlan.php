<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatingPlan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'layout_data' => 'array',
    ];
}
