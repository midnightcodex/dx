<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use App\Core\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'hr.payslips';

    protected $fillable = [
        'organization_id',
        'payroll_id',
        'employee_id',
        'gross_earnings',
        'total_deductions',
        'net_pay',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function items()
    {
        return $this->hasMany(PayslipItem::class, 'payslip_id');
    }
}
