<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Department;
use App\Modules\Auth\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'user']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%")
                    ->orWhere('employee_code', 'ilike', "%{$search}%");
            });
        }

        $employees = $query->paginate(15);

        return Inertia::render('HR/Employees', [
            'employees' => $employees,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('HR/EmployeeCreate', [
            'departments' => Department::all(['id', 'name']),
            'users' => User::doesntHave('employee')->get(['id', 'name', 'email']), // Only unlinked users
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:hr.employees,email',
            'department_id' => 'required|exists:hr.departments,id',
            'employee_code' => 'required|unique:hr.employees,employee_code',
            'date_of_joining' => 'required|date',
            'designation' => 'required|string',
            'user_id' => 'nullable|exists:auth.users,id',
        ]);

        Employee::create(array_merge($validated, [
            'organization_id' => auth()->user()->organization_id,
            'status' => 'ACTIVE'
        ]));

        return redirect()->route('hr.employees.index')->with('message', 'Employee added successfully.');
    }
}
