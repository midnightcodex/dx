<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Services\UomService;
use Illuminate\Http\Request;

class UomController extends Controller
{
    public function __construct(private UomService $service)
    {
    }

    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $uoms = $this->service->listActive($orgId);

        return $this->success($uoms, 'UOMs fetched');
    }
}
