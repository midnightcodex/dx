<?php

namespace App\Modules\Sales\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class SalesReturnLine extends Model
{
    use HasUuid;

    protected $table = 'sales.sales_return_lines';

    protected $fillable = [
        'sales_return_id',
        'line_number',
        'delivery_note_line_id',
        'item_id',
        'returned_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'batch_id',
        'disposition',
    ];
}
