<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLandlordIsAuthenticated
{
    /**
     * Ensure the current user is an authenticated landlord.
     * This is a thin wrapper around Laravel's 'auth' middleware so the
     * route file can express intent in a readable way.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
