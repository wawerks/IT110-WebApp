<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Check if the user is authenticated
        if (auth()->guest()) {
            // Redirect to the login page if the user is not authenticated
            return redirect()->route('login');
        }

        // Check if the authenticated user has the required role
        if (auth()->user()->role !== $role) {
            // If the user does not have the required role, redirect to a different page
            return redirect('/'); // Or to a specific page, like the dashboard
        }

        return $next($request);
    }
}
