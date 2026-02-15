<?php

namespace App\Modules\Procurement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_number' => 'required|string|max:50',
            'vendor_id' => 'required|uuid',
            'purchase_order_id' => 'nullable|uuid',
            'grn_id' => 'nullable|uuid',
            'invoice_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
        ];
    }
}
