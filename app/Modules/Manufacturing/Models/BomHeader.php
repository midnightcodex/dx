<?php

namespace App\Modules\Manufacturing\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use App\Core\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

/**
 * BOM Header model - Bill of Materials.
 * Uses the 'manufacturing.bom_headers' table in PostgreSQL.
 */
class BomHeader extends Model
{
    use HasUuid, BelongsToOrganization, HasAuditFields;

    protected $table = 'manufacturing.bom_headers';

    protected $fillable = [
        'organization_id',
        'item_id',
        'bom_number',
        'version',
        'is_active',
        'effective_from',
        'effective_to',
        'base_quantity',
        'uom_id',
        'routing_id',
        'estimated_cost',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'base_quantity' => 'decimal:4',
        'estimated_cost' => 'decimal:4',
    ];

    /**
     * Get the finished good item this BOM produces.
     */
    public function item()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Item::class, 'item_id');
    }

    /**
     * Get the BOM lines (components).
     */
    public function lines()
    {
        return $this->hasMany(BomLine::class, 'bom_header_id');
    }

    /**
     * Get the routing for this BOM.
     */
    public function routing()
    {
        return $this->belongsTo(Routing::class, 'routing_id');
    }

    /**
     * Scope for active BOMs only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the active BOM for an item.
     */
    public static function getActiveForItem(string $itemId)
    {
        return static::where('item_id', $itemId)
            ->where('is_active', true)
            ->latest('version')
            ->first();
    }

    /**
     * Explode BOM to get full component tree.
     */
    public function explode(float $quantity = 1, int $maxDepth = 10): array
    {
        $components = [];
        $multiplier = $quantity / (float) $this->base_quantity;

        foreach ($this->lines as $line) {
            $requiredQty = (float) $line->quantity_per_unit * $multiplier;
            $requiredQty *= (1 + ((float) $line->scrap_percentage / 100));

            $component = [
                'item_id' => $line->component_item_id,
                'item' => $line->componentItem,
                'quantity' => $requiredQty,
                'level' => 1,
                'operation_sequence' => $line->operation_sequence,
            ];

            // Check if component has its own BOM (sub-assembly)
            if ($maxDepth > 1) {
                $subBom = static::getActiveForItem($line->component_item_id);
                if ($subBom) {
                    $component['sub_components'] = $subBom->explode($requiredQty, $maxDepth - 1);
                }
            }

            $components[] = $component;
        }

        return $components;
    }
}
