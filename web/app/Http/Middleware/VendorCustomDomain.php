<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\VendorSetting;

class VendorCustomDomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $mainDomain = config('app.url');
        $mainHost = parse_url($mainDomain, PHP_URL_HOST);

        if ($host !== $mainHost) {
            $settings = VendorSetting::where('custom_domain', $host)->first();
            if ($settings) {
                // Store vendor context in request
                $request->merge(['vendor_context' => $settings->vendor_id]);
                
                // You could also set a global config or share with Inertia
                // \Inertia\Inertia::share('vendor_branding', $settings->vendor->name);
            }
        }

        return $next($request);
    }
}
