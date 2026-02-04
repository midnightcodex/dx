<?php

namespace App\Modules\HR\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PayslipItem extends Model
{
    use HasUuid;

    protected $table = 'hr.payslip_items';

    protected $fillable = [
        'payslip_id',
        'component_name',
        'type',
        'amount',
    ];
}
