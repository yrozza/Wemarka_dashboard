<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class EnsureUserIsAuthenticated
{
    // public function handle(Request $request, Closure $next)
    // {
    //     if (!auth()->check()) {
    //         // Return a 403 Forbidden response if the user is not authenticated
    //         return response()->json(['message' => 'Forbidden'], 403);
    //     }

    //     return $next($request);
    // }
}



