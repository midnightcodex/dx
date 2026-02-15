<?php

namespace App\Http\Controllers;

use App\Core\Errors\ErrorCodes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        ?array $meta = null
    ): JsonResponse {
        $requestId = request()?->attributes?->get('request_id') ?? request()?->header('X-Request-Id');

        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'request_id' => $requestId,
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function error(
        string $message,
        int $status = 400,
        ?array $errors = null,
        ?string $errorCode = null
    ): JsonResponse {
        if ($errorCode === null) {
            $errorCode = $status >= 500 ? ErrorCodes::SERVER_ERROR : ErrorCodes::REQUEST_ERROR;
        }

        $requestId = request()?->attributes?->get('request_id') ?? request()?->header('X-Request-Id');

        $payload = [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'request_id' => $requestId,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    protected function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
