<?php

namespace App\Modules\Shared\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Crud\CrudService;
use App\Modules\Shared\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    private CrudService $service;

    public function __construct()
    {
        $this->service = new CrudService(SystemSetting::class);
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'System settings fetched', 200, $this->paginationMeta($paginated));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|max:100',
            'setting_value' => 'nullable|string',
            'setting_type' => 'nullable|string|max:20',
            'module' => 'nullable|string|max:50',
        ]);

        $setting = $this->service->create($request->user()->organization_id, $request->user()->id, $validated);
        return $this->success($setting, 'System setting created', 201);
    }

    public function show(Request $request, string $id)
    {
        $setting = $this->service->find($request->user()->organization_id, $id);
        return $this->success($setting, 'System setting retrieved');
    }

    public function update(Request $request, string $id)
    {
        $setting = $this->service->find($request->user()->organization_id, $id);
        $validated = $request->validate([
            'setting_value' => 'nullable|string',
            'setting_type' => 'nullable|string|max:20',
            'module' => 'nullable|string|max:50',
        ]);

        $setting = $this->service->update($setting, $request->user()->id, $validated);
        return $this->success($setting, 'System setting updated');
    }

    public function destroy(Request $request, string $id)
    {
        $setting = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($setting);
        return $this->success(null, 'System setting deleted');
    }
}
