<?php

namespace App\Modules\Compliance\Services;

use App\Modules\Compliance\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogService
{
    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return AuditLog::query()
            ->where('organization_id', $organizationId)
            ->latest('changed_at')
            ->paginate($perPage);
    }

    public function find(string $organizationId, string $id): AuditLog
    {
        return AuditLog::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
    }
}
