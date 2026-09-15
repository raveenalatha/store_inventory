<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Build a successful JSON API response.
     *
     * @param  mixed  $data
     */
    public static function success($message, $data = [], $status = 200)
    {
        return new JsonResponse([
            'message' => $message,
            'data' => $data,
            'errors' => (object) [],
        ], $status);
    }

    /**
     * Build an error JSON API response.
     *
     * @param  mixed  $errors
     */
    public static function error($message, $errors = [], $status = 400)
    {
        return new JsonResponse([
            'message' => $message,
            'data' => (object) [],
            'errors' => empty($errors) ? (object) [] : $errors,
        ], $status);
    }
}
