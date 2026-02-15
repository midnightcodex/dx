<?php

namespace App\Modules\Shared\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'shared.approval_workflows';

    protected $fillable = [
        'organization_id',
        'workflow_name',
        'document_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function steps()
    {
        return $this->hasMany(ApprovalWorkflowStep::class, 'workflow_id')->orderBy('step_number');
    }
}
