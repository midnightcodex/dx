<?php

namespace App\Modules\Reports\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class ReportDefinition extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'reports.report_definitions';

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'report_code',
        'report_name',
        'report_category',
        'sql_query',
        'parameters',
        'is_system_report',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'is_system_report' => 'boolean',
        'created_at' => 'datetime',
    ];
}
