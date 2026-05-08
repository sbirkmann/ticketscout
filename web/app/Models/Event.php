<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'tags' => 'array',
            'gallery_images' => 'array',
        ];
    }

    // Get all sibling events (same series = same parent or children of the same parent)
    public function siblingDates()
    {
        return $this->hasMany(Event::class, 'parent_event_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function parentEvent()
    {
        return $this->belongsTo(Event::class, 'parent_event_id');
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function ticketCategories()
    {
        return $this->hasMany(TicketCategory::class);
    }

    public function addons()
    {
        return $this->hasMany(Addon::class);
    }

    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'artist_event')
            ->withPivot('role')
            ->withTimestamps();
    }
}
