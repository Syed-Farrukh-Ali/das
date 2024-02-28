<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminAuth
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
        if (auth()->user()->isAdmin()) {
            return $next($request);
        }

        $response = [
            'metadata' => [
                'responseCode' => 403,
                'success' => false,
                'message' => 'You are not authorized to perform this action',
            ],
            'payload' => [],
        ];

        return response()->json($response);
    }
}
