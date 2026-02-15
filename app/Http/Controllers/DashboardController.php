<?php

namespace App\Http\Controllers;

use App\Modules\Shared\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service)
    {
    }

    /**
     * Display the dashboard with real data.
     */
    public function index()
    {
        $orgId = auth()->user()->organization_id;
        $data = $this->service->build($orgId);

        return Inertia::render('Dashboard', $data);
    }
}
