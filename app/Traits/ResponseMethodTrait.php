<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseMethodTrait
{

    protected function sendError($errorMessages, $result = [], $code = 404): JsonResponse
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

    protected function sendResponse($result, $message, $code = 200): JsonResponse
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

    protected function serverErrorMessage(): JsonResponse
    {
        return 'Something went wrong, internal server error';
    }
}
