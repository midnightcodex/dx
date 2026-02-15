<?php

namespace App\Modules\Shared\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service)
    {
    }

    public function show(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $data = $this->service->build($orgId);

        return $this->success($data, 'Dashboard data');
    }
}
