<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class ApiJsonController extends Controller
{
    /**
     * Build a successful API JSON response.
     */
    protected function success(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        $payload = ['success' => true, 'message' => $message];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    /**
     * Build a failure API JSON response.
     */
    protected function error(string $message, mixed $errors = null, int $status = 400): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
