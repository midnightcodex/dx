<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveType;
use App\Modules\HR\Models\LeaveAllocation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $employee = $user->employee; // Assuming relationship exists on User model or we query it

        // If simple User link not set up on User model yet:
        if (!$employee) {
            $employee = \App\Modules\HR\Models\Employee::where('user_id', $user->id)->first();
        }

        if (!$employee) {
            // Admin view or error
            return Inertia::render('HR/Leaves', [
                'requests' => LeaveRequest::with(['employee', 'type'])->latest()->paginate(10),
                'leaveTypes' => LeaveType::all(),
                'balances' => [],
                'isEmployee' => false,
            ]);
        }

        // Employee View
        $requests = LeaveRequest::with(['type', 'approver'])
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate(10);

        $balances = LeaveAllocation::with('type')
            ->where('employee_id', $employee->id)
            ->where('year', date('Y'))
            ->get();

        return Inertia::render('HR/Leaves', [
            'requests' => $requests,
            'leaveTypes' => LeaveType::all(),
            'balances' => $balances,
            'isEmployee' => true,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $employee = \App\Modules\HR\Models\Employee::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:hr.leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        $days = (strtotime($validated['end_date']) - strtotime($validated['start_date'])) / (60 * 60 * 24) + 1;

        // Check Balance
        $allocation = LeaveAllocation::where('employee_id', $employee->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', date('Y'))
            ->first();

        if (!$allocation || ($allocation->days_allocated - $allocation->days_used) < $days) {
            return back()->withErrors(['leave_type_id' => 'Insufficient leave balance.']);
        }

        LeaveRequest::create(array_merge($validated, [
            'organization_id' => $user->organization_id,
            'employee_id' => $employee->id,
            'days_requested' => $days,
            'status' => 'PENDING',
        ]));

        return back()->with('message', 'Leave requested successfully.');
    }

    public function update(Request $request, $id)
    {
        // Approval/Rejection Logic
        $validated = $request->validate([
            'status' => 'required|in:APPROVED,REJECTED',
            'rejection_reason' => 'required_if:status,REJECTED',
        ]);

        return DB::transaction(function () use ($id, $validated) {
            $req = LeaveRequest::findOrFail($id);

            if ($req->status !== 'PENDING')
                return back();

            $req->update([
                'status' => $validated['status'],
                'approver_id' => auth()->id(),
                'rejection_reason' => $validated['rejection_reason'] ?? null,
            ]);

            if ($validated['status'] === 'APPROVED') {
                // Deduct Balance
                $allocation = LeaveAllocation::where('employee_id', $req->employee_id)
                    ->where('leave_type_id', $req->leave_type_id)
                    ->where('year', date('Y'))
                    ->first();

                if ($allocation) {
                    $allocation->increment('days_used', $req->days_requested);
                }
            }

            return back()->with('message', "Leave request {$validated['status']}");
        });
    }
}
