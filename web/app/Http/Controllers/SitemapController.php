<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Location;
use App\Models\Artist;

class SitemapController extends Controller
{
    public function index()
    {
        $events = Event::where('status', 'published')->orderBy('created_at', 'desc')->get();
        $locations = Location::where('is_global', true)->where('is_approved', true)->get();
        $artists = Artist::where('is_published', true)->get();

        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . view('sitemap.index', [
            'events' => $events,
            'locations' => $locations,
            'artists' => $artists,
        ])->render();

        return response($xmlContent)->header('Content-Type', 'text/xml');
    }
}
