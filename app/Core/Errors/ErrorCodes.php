<?php

namespace App\Core\Errors;

final class ErrorCodes
{
    // Generic
    public const REQUEST_ERROR = 'REQUEST_ERROR';
    public const SERVER_ERROR = 'SERVER_ERROR';

    // Auth & validation
    public const VALIDATION_ERROR = 'VALIDATION_ERROR';
    public const UNAUTHENTICATED = 'UNAUTHENTICATED';
    public const FORBIDDEN = 'FORBIDDEN';
    public const INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';

    // HTTP / routing
    public const NOT_FOUND = 'NOT_FOUND';
    public const TOO_MANY_REQUESTS = 'TOO_MANY_REQUESTS';
    public const HTTP_ERROR = 'HTTP_ERROR';

    // Manufacturing
    public const WORK_ORDER_RELEASE_INVALID_STATUS = 'WORK_ORDER_RELEASE_INVALID_STATUS';
}
