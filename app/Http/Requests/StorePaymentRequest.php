<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Payment;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   public function authorize(): bool
{
      // هذا هو التعديل الأهم
        $invoice = $this->route('invoice');
        return $this->user()->can('create', [Payment::class, $invoice]);
}

public function rules(): array
{
    return [
        //'invoice_id' => 'required|exists:invoices,id',
        'amount' => 'required|numeric|gt:0', // المبلغ يجب أن يكون أكبر من صفر
        'payment_date' => 'required|date',
        'payment_method' => 'required|string|in:cash,card,bank_transfer',
        'transaction_reference' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
    ];
}

}
