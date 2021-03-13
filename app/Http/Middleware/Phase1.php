<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Phase1
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
        if (config('app.app_phase') > 0) {
            return $next($request);
        } else {
            abort(401);
        }
    }
}
