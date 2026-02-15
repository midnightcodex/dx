<?php

namespace App\Modules\Shared\Models;

use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use HasUuid, BelongsToOrganization;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';

    protected $table = 'shared.approval_requests';

    protected $fillable = [
        'organization_id',
        'entity_type',
        'entity_id',
        'reference_type',
        'reference_id',
        'workflow_id',
        'from_status',
        'to_status',
        'current_step',
        'total_steps',
        'status',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'comments',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];
}
