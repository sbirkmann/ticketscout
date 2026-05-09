<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function impersonate(User $user)
    {
        // Prevent impersonating another superadmin to avoid privilege escalation loops
        if ($user->hasRole('superadmin')) {
            return back()->with('error', 'Cannot impersonate another Superadmin.');
        }

        // Store original admin ID in session
        session(['impersonator_id' => auth()->id()]);
        
        // Log in as the selected user
        Auth::login($user);
        
        return redirect()->route('vendor.dashboard')->with('success', 'You are now impersonating ' . $user->name);
    }

    public function leave()
    {
        if (session()->has('impersonator_id')) {
            $adminId = session('impersonator_id');
            session()->forget('impersonator_id');
            
            $admin = User::find($adminId);
            if ($admin) {
                Auth::login($admin);
                return redirect()->route('superadmin.dashboard')->with('success', 'Welcome back to Superadmin.');
            }
        }
        
        return redirect('/')->with('error', 'No impersonation session found.');
    }
}
