<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        if (Auth::attempt(['email' => request()->email, 'password' => request()->password])) {
            $user = Auth::user();
            $roles = $user->roles()->pluck('name')->toArray();
            if (array_intersect($roles, ['Student'])) {
                $user->load('student.studentClass', 'student.campus', 'student.globalSection', 'student.session', 'student.hostel');
            }
            $response['user'] = new UserResource($user);
            $response['token'] = $user->createToken('Laravel Password Grant Client')->plainTextToken;

            return $this->sendResponse($response, 'User authenticated successfully', 200);
        }

        return $this->sendError([], 'These credentials do not match our records.', 401);
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $code = 200)
    {
        $response = [
            'metadata' => [
                'responseCode' => $code,
                'success' => true,
                'message' => $message,
            ],
            'payload' => $result,
        ];

        return response()->json($response, 200);
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

    public function logout(Request $request)
    {
        return $request->user()->currentAccessToken()->delete();
    }
}
