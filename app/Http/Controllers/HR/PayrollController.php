<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Payroll;
use App\Modules\HR\Models\Payslip;
use App\Modules\HR\Models\PayslipItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = Payroll::orderby('year', 'desc')->orderby('month', 'desc')->paginate(12);
        return Inertia::render('HR/Payroll', [
            'payrolls' => $payrolls
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2025',
        ]);

        if (Payroll::where('month', $validated['month'])->where('year', $validated['year'])->exists()) {
            return back()->withErrors(['month' => 'Payroll already processed for this period.']);
        }

        return DB::transaction(function () use ($validated) {
            $payroll = Payroll::create([
                'organization_id' => auth()->user()->organization_id,
                'month' => $validated['month'],
                'year' => $validated['year'],
                'status' => 'PROCESSED',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
            ]);

            $employees = Employee::with('salaryStructure.component')->where('status', 'ACTIVE')->get();
            $count = 0;

            foreach ($employees as $emp) {
                // Skip if no structure
                if ($emp->salaryStructure->isEmpty())
                    continue;

                $gross = 0;
                $deductions = 0;
                $items = [];

                foreach ($emp->salaryStructure as $struct) {
                    $amount = $struct->amount;
                    $comp = $struct->component;

                    if ($comp->type === 'EARNING') {
                        $gross += $amount;
                    } else {
                        $deductions += $amount;
                    }

                    $items[] = [
                        'component_name' => $comp->name,
                        'type' => $comp->type,
                        'amount' => $amount
                    ];
                }

                $net = $gross - $deductions;

                $payslip = Payslip::create([
                    'organization_id' => $payroll->organization_id,
                    'payroll_id' => $payroll->id,
                    'employee_id' => $emp->id,
                    'gross_earnings' => $gross,
                    'total_deductions' => $deductions,
                    'net_pay' => $net,
                    'status' => 'GENERATED',
                ]);

                foreach ($items as $item) {
                    PayslipItem::create(array_merge($item, ['payslip_id' => $payslip->id]));
                }

                $count++;
            }

            return back()->with('message', "Payroll processed successfully for {$count} employees.");
        });
    }

    public function show($id)
    {
        // Show all payslips for a payroll run
        $payroll = Payroll::findOrFail($id);
        $payslips = Payslip::with('employee')->where('payroll_id', $id)->get();

        return Inertia::render('HR/PayrollDetail', [
            'payroll' => $payroll,
            'payslips' => $payslips
        ]);
    }
}
