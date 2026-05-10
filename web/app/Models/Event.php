<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Event extends Model
{
    use LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty();
    }
    
    protected $appends = ['is_favorited', 'average_rating'];

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

    public function waitlists()
    {
        return $this->hasMany(Waitlist::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites', 'event_id', 'user_id')->withTimestamps();
    }

    public function promoCodes()
    {
        return $this->hasMany(PromoCode::class);
    }

    public function getIsFavoritedAttribute()
    {
        if (!auth()->check())
            return false;

        // This is not optimized for N+1 if loading many events. 
        // For N+1 prevention we should eager load a constrained relation or use `withExists`
        return $this->favorites()->where('user_id', auth()->id())->exists();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->where('is_approved', true)->avg('rating') ?? 0, 1);
    }
}
