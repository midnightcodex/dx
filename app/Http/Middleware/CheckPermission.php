<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Enforce permission by module + inferred action.
     *
     * Permission slug format: {module}-{action}
     * Actions: view, create, edit, delete, approve
     */
    public function handle(Request $request, Closure $next, string $module)
    {
        $user = $request->user();

        if (!$user) {
            throw new AuthorizationException('Unauthenticated.');
        }

        // Super admins bypass module permission checks.
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        $action = $this->resolveAction($request);
        $permission = strtolower($module) . '-' . $action;

        if (!$user->hasPermission($permission)) {
            throw new AuthorizationException("Missing required permission: {$permission}");
        }

        return $next($request);
    }

    protected function resolveAction(Request $request): string
    {
        $method = strtoupper($request->method());

        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return 'view';
        }

        if ($method === 'DELETE') {
            return 'delete';
        }

        if (in_array($method, ['PUT', 'PATCH'], true)) {
            return 'edit';
        }

        if ($method === 'POST') {
            $path = strtolower('/' . trim($request->path(), '/'));

            $approvalActions = [
                '/approve',
                '/submit',
                '/release',
                '/dispatch',
                '/complete',
                '/cancel',
                '/assign',
                '/resolve',
                '/post',
                '/reject',
                '/generate-work-orders',
                '/record-production',
                '/issue-materials',
                '/allocate-materials',
                '/recover',
                '/dispose',
                '/export-invoices',
                '/export-stock-valuation',
                '/print-batch',
                '/clock-in',
                '/clock-out',
            ];

            foreach ($approvalActions as $segment) {
                if (str_ends_with($path, $segment) || str_contains($path, $segment . '/')) {
                    return 'approve';
                }
            }

            return 'create';
        }

        return 'view';
    }
}
