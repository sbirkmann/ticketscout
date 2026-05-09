<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'email',
        'status',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
