<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Auth\Models\User;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveType;
use App\Modules\HR\Models\LeaveAllocation;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\SalaryComponent;
use App\Modules\HR\Models\EmployeeSalaryStructure;
use App\Modules\HR\Models\Payroll;
use App\Modules\HR\Models\Payslip;

class HrPayrollTest extends TestCase
{
    private $user;
    private $orgId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('email', 'admin@examp.com')->first();
        $this->orgId = $this->user->organization_id;
        $this->actingAs($this->user);
    }

    public function test_employee_creation_and_linking()
    {
        $dept = Department::create(['organization_id' => $this->orgId, 'name' => 'IT']);

        $response = $this->post(route('hr.employees.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe.test@examp.com', // Unique email
            'department_id' => $dept->id,
            'employee_code' => 'TEST-EMP-001',
            'designation' => 'Dev',
            'date_of_joining' => '2025-01-01',
            'user_id' => $this->user->id // Linking to admin user for test
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('hr.employees', ['email' => 'john.doe.test@examp.com']);
    }

    public function test_leave_flow()
    {
        // 1. Setup Employee & Allocation
        $dept = Department::firstOrCreate(['organization_id' => $this->orgId, 'name' => 'IT']);
        $emp = Employee::create([
            'organization_id' => $this->orgId,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@examp.com',
            'employee_code' => 'TEST-EMP-002',
            'department_id' => $dept->id,
            'user_id' => $this->user->id // Link to current user to simulate applying
        ]);

        $type = LeaveType::create(['organization_id' => $this->orgId, 'name' => 'Sick', 'default_days_per_year' => 10]);
        LeaveAllocation::create([
            'organization_id' => $this->orgId,
            'employee_id' => $emp->id,
            'leave_type_id' => $type->id,
            'days_allocated' => 10,
            'days_used' => 0,
            'year' => date('Y')
        ]);

        // 2. Apply for Leave (2 days)
        $this->post(route('hr.leaves.store'), [
            'leave_type_id' => $type->id,
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-02'),
            'reason' => 'Sick'
        ]);

        $this->assertDatabaseHas('hr.leave_requests', ['employee_id' => $emp->id, 'days_requested' => 2, 'status' => 'PENDING']);

        // 3. Approve Leave
        $req = \App\Modules\HR\Models\LeaveRequest::where('employee_id', $emp->id)->first();
        $this->post(route('hr.leaves.update', $req->id), ['status' => 'APPROVED']);

        // 4. Verify Balance Deduction
        $this->assertDatabaseHas('hr.leave_allocations', [
            'employee_id' => $emp->id,
            'days_used' => 2
        ]);
    }

    public function test_payroll_processing()
    {
        // 1. Setup Employee with Salary Structure
        $dept = Department::firstOrCreate(['organization_id' => $this->orgId, 'name' => 'IT']);
        $emp = Employee::create([
            'organization_id' => $this->orgId,
            'first_name' => 'Rich',
            'last_name' => 'Guy',
            'email' => 'rich@examp.com',
            'employee_code' => 'TEST-EMP-003',
            'department_id' => $dept->id,
            'status' => 'ACTIVE'
        ]);

        $basic = SalaryComponent::create(['organization_id' => $this->orgId, 'name' => 'Basic', 'type' => 'EARNING']);
        $tax = SalaryComponent::create(['organization_id' => $this->orgId, 'name' => 'Tax', 'type' => 'DEDUCTION']);

        EmployeeSalaryStructure::create(['organization_id' => $this->orgId, 'employee_id' => $emp->id, 'salary_component_id' => $basic->id, 'amount' => 10000]);
        EmployeeSalaryStructure::create(['organization_id' => $this->orgId, 'employee_id' => $emp->id, 'salary_component_id' => $tax->id, 'amount' => 1000]);

        // 2. Run Payroll
        $this->post(route('hr.payroll.store'), [
            'month' => 5,
            'year' => 2026
        ]);

        // 3. Verify Payslip
        $this->assertDatabaseHas('hr.payrolls', ['month' => 5, 'year' => 2026, 'status' => 'PROCESSED']);

        $payslip = Payslip::where('employee_id', $emp->id)->first();
        $this->assertNotNull($payslip);
        $this->assertEquals(10000, $payslip->gross_earnings);
        $this->assertEquals(1000, $payslip->total_deductions);
        $this->assertEquals(9000, $payslip->net_pay);
    }
}
