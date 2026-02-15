<?php

namespace App\Modules\Shared\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflowStep extends Model
{
    use HasUuid;

    protected $table = 'shared.approval_workflow_steps';

    protected $fillable = [
        'workflow_id',
        'step_number',
        'role_id',
        'min_amount',
        'max_amount',
    ];
}
