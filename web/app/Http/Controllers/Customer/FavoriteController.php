<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = auth()->user()->favoriteEvents()
            ->with(['location', 'ticketCategories' => function($q) {
                $q->orderBy('price', 'asc');
            }])
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->paginate(12);

        return Inertia::render('Customer/Favorites/Index', [
            'favorites' => $favorites
        ]);
    }

    public function toggle(Event $event)
    {
        $user = auth()->user();
        
        if ($user->favoriteEvents()->where('event_id', $event->id)->exists()) {
            $user->favoriteEvents()->detach($event->id);
            $isFavorite = false;
        } else {
            $user->favoriteEvents()->attach($event->id);
            $isFavorite = true;
        }

        return response()->json([
            'is_favorite' => $isFavorite
        ]);
    }
}
