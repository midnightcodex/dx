<?php

namespace App\Modules\Shared\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Crud\CrudService;
use App\Modules\Shared\Models\NumberSeries;
use Illuminate\Http\Request;

class NumberSeriesController extends Controller
{
    private CrudService $service;

    public function __construct()
    {
        $this->service = new CrudService(NumberSeries::class);
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'Number series fetched', 200, $this->paginationMeta($paginated));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string|max:100',
            'prefix' => 'nullable|string|max:20',
            'suffix' => 'nullable|string|max:20',
            'format' => 'nullable|string|max:100',
            'current_number' => 'nullable|integer',
            'padding' => 'nullable|integer|min:1|max:12',
            'include_date' => 'nullable|boolean',
            'date_format' => 'nullable|string|max:20',
            'reset_on_date_change' => 'nullable|boolean',
        ]);

        $series = $this->service->create($request->user()->organization_id, $request->user()->id, $validated);
        return $this->success($series, 'Number series created', 201);
    }

    public function show(Request $request, string $id)
    {
        $series = $this->service->find($request->user()->organization_id, $id);
        return $this->success($series, 'Number series retrieved');
    }

    public function update(Request $request, string $id)
    {
        $series = $this->service->find($request->user()->organization_id, $id);
        $validated = $request->validate([
            'entity_type' => 'sometimes|string|max:100',
            'prefix' => 'nullable|string|max:20',
            'suffix' => 'nullable|string|max:20',
            'format' => 'nullable|string|max:100',
            'current_number' => 'nullable|integer',
            'padding' => 'nullable|integer|min:1|max:12',
            'include_date' => 'nullable|boolean',
            'date_format' => 'nullable|string|max:20',
            'reset_on_date_change' => 'nullable|boolean',
        ]);

        $series = $this->service->update($series, $request->user()->id, $validated);
        return $this->success($series, 'Number series updated');
    }

    public function destroy(Request $request, string $id)
    {
        $series = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($series);
        return $this->success(null, 'Number series deleted');
    }
}
