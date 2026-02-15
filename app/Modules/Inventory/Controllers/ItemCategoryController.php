<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Services\ItemCategoryService;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function __construct(private ItemCategoryService $service)
    {
    }

    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $categories = $this->service->listActive($orgId);

        return $this->success($categories, 'Item categories fetched');
    }
}
