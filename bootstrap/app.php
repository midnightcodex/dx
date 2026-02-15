<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Core\Errors\ErrorCodes;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        $middleware->web(append: [
            \Inertia\Middleware::class,
        ]);

        $middleware->api(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\RequestId::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, $request) {
            if (!$request->expectsJson() && !$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Validation failed.',
                'error_code' => ErrorCodes::VALIDATION_ERROR,
                'request_id' => $request->attributes->get('request_id') ?? $request->header('X-Request-Id'),
                'errors' => $e->errors(),
            ], $e->status);
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if (!$request->expectsJson() && !$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Unauthenticated.',
                'error_code' => ErrorCodes::UNAUTHENTICATED,
                'request_id' => $request->attributes->get('request_id') ?? $request->header('X-Request-Id'),
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e, $request) {
            if (!$request->expectsJson() && !$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Forbidden.',
                'error_code' => ErrorCodes::FORBIDDEN,
                'request_id' => $request->attributes->get('request_id') ?? $request->header('X-Request-Id'),
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if (!$request->expectsJson() && !$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
                'error_code' => ErrorCodes::NOT_FOUND,
                'request_id' => $request->attributes->get('request_id') ?? $request->header('X-Request-Id'),
            ], 404);
        });

        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            if (!$request->expectsJson() && !$request->is('api/*')) {
                return null;
            }

            $status = $e->getStatusCode();
            $message = trim((string) $e->getMessage());
            $headers = $e->getHeaders();

            if ($message === '') {
                $message = match ($status) {
                    401 => 'Unauthenticated.',
                    403 => 'Forbidden.',
                    404 => 'Not Found.',
                    429 => 'Too many requests.',
                    default => 'Request error.',
                };
            }

            $errorCode = match ($status) {
                401 => ErrorCodes::UNAUTHENTICATED,
                403 => ErrorCodes::FORBIDDEN,
                404 => ErrorCodes::NOT_FOUND,
                429 => ErrorCodes::TOO_MANY_REQUESTS,
                default => ErrorCodes::HTTP_ERROR,
            };

            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => $errorCode,
                'request_id' => $request->attributes->get('request_id') ?? $request->header('X-Request-Id'),
            ], $status, $headers);
        });

        $exceptions->render(function (\Throwable $e, $request) {
            if (!$request->expectsJson() && !$request->is('api/*')) {
                return null;
            }

            Log::error('Unhandled exception', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => optional($request->user())->id,
                'route' => optional($request->route())->getName(),
                'request_id' => $request->attributes->get('request_id') ?? $request->header('X-Request-Id'),
                'user_agent' => $request->userAgent(),
            ]);

            $message = config('app.debug') ? $e->getMessage() : 'Server Error.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => ErrorCodes::SERVER_ERROR,
                'request_id' => $request->attributes->get('request_id') ?? $request->header('X-Request-Id'),
            ], 500);
        });
    })->create();
