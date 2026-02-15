<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Shared\Models\ItemCategory;
use Illuminate\Support\Collection;

class ItemCategoryService
{
    public function listActive(string $organizationId): Collection
    {
        return ItemCategory::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(fn($category) => [
                'id' => $category->id,
                'category_code' => $category->code,
                'category_name' => $category->name,
                'code' => $category->code,
                'name' => $category->name,
            ]);
    }
}
