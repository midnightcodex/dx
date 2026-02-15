<?php

namespace App\Modules\Compliance\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'compliance.certifications';

    protected $fillable = [
        'organization_id',
        'certification_type',
        'certification_number',
        'issuing_authority',
        'issue_date',
        'expiry_date',
        'scope',
        'certificate_file_path',
        'status',
        'next_audit_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'next_audit_date' => 'date',
    ];
}
