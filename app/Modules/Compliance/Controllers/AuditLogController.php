<?php

namespace App\Modules\Compliance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Compliance\Resources\AuditLogResource;
use App\Modules\Compliance\Services\AuditLogService;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    private AuditLogService $service;

    public function __construct()
    {
        $this->service = new AuditLogService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            AuditLogResource::collection($paginated->items()),
            'Audit logs fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function show(Request $request, string $id)
    {
        $log = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new AuditLogResource($log), 'Audit log retrieved');
    }
}
