<?php

namespace App\Modules\Shared\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Models\ApprovalRequest;
use App\Modules\Shared\Models\ApprovalWorkflow;
use App\Modules\Shared\Services\ApprovalWorkflowService;
use Illuminate\Http\Request;

class ApprovalWorkflowController extends Controller
{
    public function __construct(private ApprovalWorkflowService $service)
    {
    }

    public function workflows(Request $request)
    {
        $workflows = ApprovalWorkflow::query()
            ->with('steps')
            ->where('organization_id', $request->user()->organization_id)
            ->orderBy('document_type')
            ->get();

        return $this->success($workflows, 'Approval workflows');
    }

    public function storeWorkflow(Request $request)
    {
        $validated = $request->validate([
            'workflow_name' => 'required|string|max:255',
            'document_type' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
            'steps' => 'required|array|min:1',
            'steps.*.step_number' => 'nullable|integer|min:1',
            'steps.*.role_id' => 'nullable|uuid',
            'steps.*.min_amount' => 'nullable|numeric',
            'steps.*.max_amount' => 'nullable|numeric',
        ]);

        $workflow = $this->service->createWorkflow($request->user()->organization_id, $validated);
        return $this->success($workflow, 'Approval workflow created', 201);
    }

    public function requestApproval(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string|max:100',
            'entity_id' => 'required|uuid',
            'from_status' => 'required|string|max:50',
            'to_status' => 'required|string|max:50',
            'amount' => 'nullable|numeric',
        ]);

        $approval = $this->service->requestApproval(
            organizationId: $request->user()->organization_id,
            requestedBy: $request->user()->id,
            entityType: $validated['entity_type'],
            entityId: $validated['entity_id'],
            fromStatus: $validated['from_status'],
            toStatus: $validated['to_status'],
            amount: $validated['amount'] ?? null
        );

        return $this->success($approval, 'Approval request created', 201);
    }

    public function pending(Request $request)
    {
        $pending = ApprovalRequest::query()
            ->where('organization_id', $request->user()->organization_id)
            ->where('status', ApprovalRequest::STATUS_PENDING)
            ->latest()
            ->get();

        return $this->success($pending, 'Pending approvals');
    }

    public function approve(Request $request, string $id)
    {
        $approval = $this->service->approve($request->user()->organization_id, $id, $request->user()->id);
        return $this->success($approval, 'Approval step completed');
    }

    public function reject(Request $request, string $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $approval = $this->service->reject($request->user()->organization_id, $id, $request->user()->id, $validated['reason']);
        return $this->success($approval, 'Approval rejected');
    }
}
