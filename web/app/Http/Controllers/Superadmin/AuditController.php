<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index()
    {
        $activities = Activity::with('causer')->latest()->paginate(50);
        
        return Inertia::render('Admin/Audit/Index', [
            'activities' => $activities
        ]);
    }
}
