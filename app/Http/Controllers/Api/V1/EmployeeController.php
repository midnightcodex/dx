<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::with('department')
            ->where('organization_id', $request->user()->organization_id)
            ->where('status', 'ACTIVE')
            ->paginate(50);

        return response()->json($employees);
    }
}
