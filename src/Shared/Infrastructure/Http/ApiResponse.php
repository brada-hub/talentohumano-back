<?php

namespace Src\Shared\Infrastructure\Http;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($data = [], string $message = 'Success', int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        return response()->json($response, $status);
    }

    public static function error(string $message = 'Error', int $status = 400, $data = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['errors'] = $data;
        }

        return response()->json($response, $status);
    }

    public static function created($data = [], string $message = 'Created'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function notFound(string $message = 'Not Found'): JsonResponse
    {
        return self::error($message, 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401);
    }
}
