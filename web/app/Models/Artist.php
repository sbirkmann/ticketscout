<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Artist extends Model
{
    protected $guarded = [];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'artist_event')
            ->withPivot('role')
            ->withTimestamps();
    }
}
