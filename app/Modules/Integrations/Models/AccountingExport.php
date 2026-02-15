<?php

namespace App\Modules\Integrations\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class AccountingExport extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'integrations.accounting_exports';

    protected $fillable = [
        'organization_id',
        'export_number',
        'export_date',
        'export_type',
        'from_date',
        'to_date',
        'reference_ids',
        'file_path',
        'file_format',
        'status',
        'exported_by',
        'exported_at',
        'error_message',
    ];

    protected $casts = [
        'export_date' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
        'exported_at' => 'datetime',
        'reference_ids' => 'array',
    ];
}
