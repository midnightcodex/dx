<?php

namespace App\Modules\Manufacturing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Manufacturing\Services\QualityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QualityController extends Controller
{
    public function __construct(private QualityService $service)
    {
    }

    public function inspections(Request $request)
    {
        $paginated = $this->service->listInspections($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'Quality inspections fetched', 200, $this->paginationMeta($paginated));
    }

    public function createInspection(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $validated = $request->validate([
            'inspection_number' => 'nullable|string|max:50',
            'template_id' => ['nullable', Rule::exists('manufacturing.quality_inspection_templates', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|uuid',
            'item_id' => ['nullable', Rule::exists('inventory.items', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'batch_id' => ['nullable', Rule::exists('inventory.batches', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'quantity_inspected' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $inspection = $this->service->createInspection($orgId, $request->user()->id, $validated);
        return $this->success($inspection, 'Quality inspection created', 201);
    }

    public function recordReadings(Request $request, string $id)
    {
        $validated = $request->validate([
            'readings' => 'required|array|min:1',
            'readings.*.parameter_id' => 'required|uuid',
            'readings.*.reading_value' => 'nullable|string|max:255',
            'readings.*.numeric_value' => 'nullable|numeric',
            'readings.*.is_within_spec' => 'nullable|boolean',
            'readings.*.notes' => 'nullable|string',
        ]);

        $inspection = $this->service->recordReadings($request->user()->organization_id, $id, $validated['readings']);
        return $this->success($inspection, 'Quality readings recorded');
    }

    public function completeInspection(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:PASSED,FAILED,CONDITIONAL',
            'overall_result' => 'required|in:ACCEPTED,REJECTED,REWORK',
            'remarks' => 'nullable|string',
        ]);

        $inspection = $this->service->completeInspection($request->user()->organization_id, $request->user()->id, $id, $validated);
        return $this->success($inspection, 'Quality inspection completed');
    }
}
