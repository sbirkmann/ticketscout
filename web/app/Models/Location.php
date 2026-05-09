<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($location) {
            if ($location->city) {
                \App\Models\City::firstOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($location->city)],
                    ['name' => $location->city]
                );
            }
        });
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
