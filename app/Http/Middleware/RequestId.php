<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestId
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = $request->header('X-Request-Id');
        if (empty($requestId)) {
            $requestId = (string) Str::uuid();
        }

        $request->attributes->set('request_id', $requestId);

        $response = $next($request);

        if (method_exists($response, 'headers')) {
            $response->headers->set('X-Request-Id', $requestId);
        }

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            if (!array_key_exists('request_id', $data)) {
                $data['request_id'] = $requestId;
                $response->setData($data);
            }
        }

        return $response;
    }
}
