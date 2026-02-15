<?php

namespace App\Modules\Manufacturing\Services;

use App\Modules\Manufacturing\Models\QualityInspection;
use App\Modules\Manufacturing\Models\QualityInspectionReading;
use App\Modules\Shared\Services\NumberSeriesService;
use Illuminate\Support\Facades\DB;

class QualityService
{
    public function __construct(private NumberSeriesService $numberSeriesService)
    {
    }

    public function listInspections(string $organizationId, int $perPage = 15)
    {
        return QualityInspection::query()
            ->with('readings')
            ->where('organization_id', $organizationId)
            ->latest()
            ->paginate($perPage);
    }

    public function createInspection(string $organizationId, string $userId, array $data): QualityInspection
    {
        return QualityInspection::create([
            'organization_id' => $organizationId,
            'inspection_number' => $data['inspection_number'] ?? $this->numberSeriesService->next(
                $organizationId,
                'QUALITY_INSPECTION',
                ['prefix' => 'QI-', 'padding' => 6]
            ),
            'template_id' => $data['template_id'] ?? null,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'item_id' => $data['item_id'] ?? null,
            'batch_id' => $data['batch_id'] ?? null,
            'quantity_inspected' => $data['quantity_inspected'] ?? 0,
            'inspection_date' => now(),
            'inspected_by' => $userId,
            'status' => 'IN_PROGRESS',
            'remarks' => $data['remarks'] ?? null,
        ]);
    }

    public function recordReadings(string $organizationId, string $inspectionId, array $readings): QualityInspection
    {
        return DB::transaction(function () use ($organizationId, $inspectionId, $readings) {
            $inspection = QualityInspection::query()
                ->where('organization_id', $organizationId)
                ->findOrFail($inspectionId);

            foreach ($readings as $reading) {
                QualityInspectionReading::create([
                    'inspection_id' => $inspection->id,
                    'parameter_id' => $reading['parameter_id'],
                    'reading_value' => $reading['reading_value'] ?? null,
                    'numeric_value' => $reading['numeric_value'] ?? null,
                    'is_within_spec' => $reading['is_within_spec'] ?? null,
                    'notes' => $reading['notes'] ?? null,
                ]);
            }

            return $inspection->refresh()->load('readings');
        });
    }

    public function completeInspection(string $organizationId, string $userId, string $inspectionId, array $data): QualityInspection
    {
        $inspection = QualityInspection::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($inspectionId);

        $inspection->status = $data['status'] ?? 'PASSED';
        $inspection->overall_result = $data['overall_result'] ?? 'ACCEPTED';
        $inspection->remarks = $data['remarks'] ?? $inspection->remarks;
        $inspection->approved_by = $userId;
        $inspection->approved_at = now();
        $inspection->save();

        return $inspection->refresh()->load('readings');
    }
}
