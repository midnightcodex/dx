<?php

namespace App\Modules\Maintenance\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class MachineDocument extends Model
{
    use HasUuid;

    protected $table = 'maintenance.machine_documents';

    public $timestamps = false;

    protected $fillable = [
        'machine_id',
        'document_type',
        'document_name',
        'document_path',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];
}
