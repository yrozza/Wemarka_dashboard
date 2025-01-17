<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Ensure this is included

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the authenticated user exists and has the 'admin' role
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request); // Allow the request to proceed
        }

        // If not admin, deny access with a 403 Forbidden response
        return response()->json(['message' => 'Forbidden. Admins only.'], 403);
    }
}
