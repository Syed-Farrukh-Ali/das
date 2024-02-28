<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HeadOfficeAuth
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
        if (auth()->user()->hasRole('Head Office')) {
            return $next($request);
        }

        return response()->json('You are not authorized');
    }
}
