<?php

namespace App\Modules\Shared\Services;

use App\Modules\Shared\Models\ApprovalRequest;
use App\Modules\Shared\Models\ApprovalWorkflow;
use App\Modules\Shared\Models\ApprovalWorkflowStep;
use Illuminate\Support\Facades\DB;

class ApprovalWorkflowService
{
    public function createWorkflow(string $organizationId, array $data): ApprovalWorkflow
    {
        return DB::transaction(function () use ($organizationId, $data) {
            $workflow = ApprovalWorkflow::create([
                'organization_id' => $organizationId,
                'workflow_name' => $data['workflow_name'],
                'document_type' => $data['document_type'],
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            foreach ($data['steps'] ?? [] as $index => $step) {
                ApprovalWorkflowStep::create([
                    'workflow_id' => $workflow->id,
                    'step_number' => $step['step_number'] ?? ($index + 1),
                    'role_id' => $step['role_id'] ?? null,
                    'min_amount' => $step['min_amount'] ?? null,
                    'max_amount' => $step['max_amount'] ?? null,
                ]);
            }

            return $workflow->load('steps');
        });
    }

    public function requestApproval(
        string $organizationId,
        string $requestedBy,
        string $entityType,
        string $entityId,
        string $fromStatus,
        string $toStatus,
        ?float $amount = null
    ): ApprovalRequest {
        $workflow = ApprovalWorkflow::query()
            ->where('organization_id', $organizationId)
            ->where('document_type', $entityType)
            ->where('is_active', true)
            ->first();

        $steps = collect();
        if ($workflow) {
            $steps = ApprovalWorkflowStep::query()
                ->where('workflow_id', $workflow->id)
                ->orderBy('step_number')
                ->get()
                ->filter(function (ApprovalWorkflowStep $step) use ($amount) {
                    if ($amount === null) {
                        return true;
                    }
                    if ($step->min_amount !== null && $amount < (float) $step->min_amount) {
                        return false;
                    }
                    if ($step->max_amount !== null && $amount > (float) $step->max_amount) {
                        return false;
                    }
                    return true;
                })
                ->values();
        }

        return ApprovalRequest::create([
            'organization_id' => $organizationId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'reference_type' => $entityType,
            'reference_id' => $entityId,
            'workflow_id' => $workflow?->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'current_step' => 1,
            'total_steps' => max(1, $steps->count()),
            'status' => ApprovalRequest::STATUS_PENDING,
            'requested_by' => $requestedBy,
            'requested_at' => now(),
        ]);
    }

    public function approve(string $organizationId, string $approvalId, string $approvedBy): ApprovalRequest
    {
        $approval = ApprovalRequest::query()
            ->where('organization_id', $organizationId)
            ->where('status', ApprovalRequest::STATUS_PENDING)
            ->findOrFail($approvalId);

        if ($approval->current_step < $approval->total_steps) {
            $approval->current_step = (int) $approval->current_step + 1;
            $approval->save();
            return $approval->refresh();
        }

        $approval->status = ApprovalRequest::STATUS_APPROVED;
        $approval->approved_by = $approvedBy;
        $approval->approved_at = now();
        $approval->save();

        return $approval->refresh();
    }

    public function reject(string $organizationId, string $approvalId, string $rejectedBy, string $reason): ApprovalRequest
    {
        $approval = ApprovalRequest::query()
            ->where('organization_id', $organizationId)
            ->where('status', ApprovalRequest::STATUS_PENDING)
            ->findOrFail($approvalId);

        $approval->status = ApprovalRequest::STATUS_REJECTED;
        $approval->rejected_by = $rejectedBy;
        $approval->rejected_at = now();
        $approval->rejection_reason = $reason;
        $approval->save();

        return $approval->refresh();
    }
}
