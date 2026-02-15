<?php

namespace App\Modules\Sales\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'return_number' => 'sometimes|string|max:50',
            'customer_id' => 'sometimes|uuid',
            'sales_order_id' => 'nullable|uuid',
            'delivery_note_id' => 'nullable|uuid',
            'return_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'reason' => 'nullable|string',
        ];
    }
}
