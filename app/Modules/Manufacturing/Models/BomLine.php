<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * BOM Line model - components in a Bill of Materials.
 * Uses the 'manufacturing.bom_lines' table in PostgreSQL.
 */
class BomLine extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'manufacturing.bom_lines';

    protected $fillable = [
        'organization_id',
        'bom_header_id',
        'line_number',
        'component_item_id',
        'quantity_per_unit',
        'uom_id',
        'scrap_percentage',
        'operation_sequence',
        'is_critical',
        'substitute_item_id',
        'notes',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'quantity_per_unit' => 'decimal:6',
        'scrap_percentage' => 'decimal:2',
        'operation_sequence' => 'integer',
        'is_critical' => 'boolean',
    ];

    /**
     * Get the BOM header.
     */
    public function bomHeader()
    {
        return $this->belongsTo(BomHeader::class, 'bom_header_id');
    }

    /**
     * Get the component item.
     */
    public function componentItem()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Item::class, 'component_item_id');
    }

    /**
     * Get the substitute item.
     */
    public function substituteItem()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Item::class, 'substitute_item_id');
    }
}
