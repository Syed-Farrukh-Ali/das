<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendError($errorMessages, $result = [], $code = 404)
    {
        $response = [
            'metadata' => [
                'responseCode' => $code,
                'success' => false,
                'message' => $errorMessages,
            ],
            'payload' => $result,
        ];

        return response()->json($response, $code);
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendResponse($result, $message, $code = 200)
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

    /**
     * 500 server error message.
     *
     * @return string
     */
    protected function serverErrorMessage()
    {
        return 'Something went wrong, internal server error';
    }
}
