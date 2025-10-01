<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  public function authorize(): bool
    {
        // هذا سيتحقق من صلاحية 'create invoices' قبل تنفيذ أي كود في المتحكم
        return $this->user()->can('create invoices');
    }

public function rules(): array
{
    // قواعد إنشاء الفاتورة تشبه عرض السعر، ولكنها قد تُنشأ تلقائياً من أمر العمل
    return [];
}

}
