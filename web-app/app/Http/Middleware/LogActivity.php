<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log user activity after the request is handled
        // if (Auth::check()) {
        //     ActivityLog::create([
        //         'user_id' => Auth::id(),
        //         'action' => $request->method() . ' ' . $request->path(), // Log the request method and path
        //         'action_time' => now(),
        //         'ip_address' => $request->ip(),
        //         'user_agent' => $request->header('User-Agent'),
        //     ]);
        // }

        return $response;
    }
}
