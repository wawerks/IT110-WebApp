<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictLostItemsAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (env('LOST_ITEMS_ACCESS') !== 'true') {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}
