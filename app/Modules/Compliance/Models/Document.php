<?php

namespace App\Modules\Compliance\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields;

    protected $table = 'compliance.documents';

    protected $fillable = [
        'organization_id',
        'document_number',
        'document_name',
        'document_type',
        'version',
        'revision_number',
        'department',
        'effective_date',
        'review_date',
        'next_review_date',
        'file_path',
        'status',
        'created_by',
        'reviewed_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'review_date' => 'date',
        'next_review_date' => 'date',
        'approved_at' => 'datetime',
    ];
}
