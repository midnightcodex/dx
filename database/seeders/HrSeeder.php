<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveType;
use App\Modules\HR\Models\SalaryComponent;
use App\Modules\Auth\Models\User;
use App\Modules\HR\Models\EmployeeSalaryStructure;
use App\Modules\HR\Models\LeaveAllocation;

class HrSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = 'org-0000-0000-0000-000000000001';
        $user = User::first();
        $userId = $user ? $user->id : null;

        if (!$userId)
            return;

        // 1. Departments
        $production = Department::create([
            'organization_id' => $organizationId,
            'name' => 'Production',
        ]);
        $sales = Department::create([
            'organization_id' => $organizationId,
            'name' => 'Sales',
        ]);
        $hr = Department::create([
            'organization_id' => $organizationId,
            'name' => 'HR',
        ]);

        // 2. Leave Types
        $sick = LeaveType::create(['organization_id' => $organizationId, 'name' => 'Sick Leave', 'default_days_per_year' => 10]);
        $casual = LeaveType::create(['organization_id' => $organizationId, 'name' => 'Casual Leave', 'default_days_per_year' => 12]);
        $earned = LeaveType::create(['organization_id' => $organizationId, 'name' => 'Earned Leave', 'default_days_per_year' => 15]);

        // 3. Employees
        // Admin
        $emp1 = Employee::create([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'department_id' => $hr->id,
            'employee_code' => 'EMP-001',
            'first_name' => 'System',
            'last_name' => 'Admin',
            'email' => $user->email,
            'designation' => 'IT Manager',
            'date_of_joining' => '2025-01-01',
            'status' => 'ACTIVE'
        ]);

        // Production Manager (Mock)
        $emp2 = Employee::create([
            'organization_id' => $organizationId,
            'department_id' => $production->id,
            'employee_code' => 'EMP-002',
            'first_name' => 'Rajesh',
            'last_name' => 'Kumar',
            'email' => 'rajesh@examp.com',
            'designation' => 'Production Manager',
            'date_of_joining' => '2025-02-15',
            'status' => 'ACTIVE'
        ]);

        // 4. Leave Allocations
        foreach ([$emp1, $emp2] as $emp) {
            foreach ([$sick, $casual, $earned] as $type) {
                LeaveAllocation::create([
                    'organization_id' => $organizationId,
                    'employee_id' => $emp->id,
                    'leave_type_id' => $type->id,
                    'days_allocated' => $type->default_days_per_year,
                    'year' => date('Y'),
                ]);
            }
        }

        // 5. Salary Components
        $basic = SalaryComponent::create(['organization_id' => $organizationId, 'name' => 'Basic Salary', 'type' => 'EARNING']);
        $hra = SalaryComponent::create(['organization_id' => $organizationId, 'name' => 'HRA', 'type' => 'EARNING']);
        $transport = SalaryComponent::create(['organization_id' => $organizationId, 'name' => 'Transport Allowance', 'type' => 'EARNING']);
        $pf = SalaryComponent::create(['organization_id' => $organizationId, 'name' => 'Provident Fund', 'type' => 'DEDUCTION']);
        $tax = SalaryComponent::create(['organization_id' => $organizationId, 'name' => 'Income Tax', 'type' => 'DEDUCTION']);

        // 6. Assign Structures
        // Admin: 1,00,000 Gross
        $this->assignSalary($emp1, [
            $basic->id => 50000,
            $hra->id => 20000,
            $transport->id => 10000,
            $pf->id => 6000, // 12% of basic
            $tax->id => 5000,
        ]);

        // Production Manager: 60,000 Gross
        $this->assignSalary($emp2, [
            $basic->id => 30000,
            $hra->id => 12000,
            $transport->id => 5000,
            $pf->id => 3600,
            $tax->id => 1000,
        ]);
    }

    private function assignSalary($emp, $components)
    {
        foreach ($components as $compId => $amount) {
            EmployeeSalaryStructure::create([
                'organization_id' => $emp->organization_id,
                'employee_id' => $emp->id,
                'salary_component_id' => $compId,
                'amount' => $amount
            ]);
        }
    }
}
