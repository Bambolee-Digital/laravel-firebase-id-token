<?php

namespace BamboleeDigital\LaravelFirebaseIdToken\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class EnhancedConfigurableAuthMiddleware
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $authOrder = !empty($guards) 
            ? $guards 
            : explode(',', Config::get('bambolee-firebase.auth_order', 'firebase,sanctum'));

        foreach ($authOrder as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::shouldUse($guard);
                return $next($request);
            }
        }

        return response()->json(['error' => 'Unauthenticated'], 401);
    }
}