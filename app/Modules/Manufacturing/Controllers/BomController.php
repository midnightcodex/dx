<?php

namespace App\Modules\Manufacturing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Manufacturing\Services\BomService;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function __construct(private BomService $service)
    {
    }

    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $boms = $this->service->listActive($orgId);

        return $this->success($boms, 'BOMs fetched');
    }
}
