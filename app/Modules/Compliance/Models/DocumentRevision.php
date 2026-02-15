<?php

namespace App\Modules\Compliance\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class DocumentRevision extends Model
{
    use HasUuid;

    protected $table = 'compliance.document_revisions';

    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'revision_number',
        'changes_description',
        'revised_by',
        'revised_at',
        'file_path',
    ];

    protected $casts = [
        'revised_at' => 'datetime',
    ];
}
