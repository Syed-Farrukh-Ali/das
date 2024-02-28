<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class School
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
        $user = Auth::user();
        $roles = $user->roles()->pluck('name')->toArray();
        $roleList = ['Head Office', 'Campus', 'Staff Member','Super Admin'];

        if (array_intersect($roles, $roleList)) {
            if ($request->campus_id and array_intersect($roles, ['Campus'])) {
                // if a campus loged in and try to work on other campuses
                if ($request->campus_id != $user->campus_id) {
                    return $this->sendError([], 'please select your own campus');
                }
            }

            return $next($request);
        }

        return $this->sendError([], ' you are not authorized for this request', 403);
    }

    public function sendError($result, $message, $code = 401)
    {
        $response = [
            'metadata' => [
                'responseCode' => $code,
                'success' => false,
                'message' => $message,
            ],
            'payload' => $result,
        ];

        return response()->json($response, 401);
    }
}
