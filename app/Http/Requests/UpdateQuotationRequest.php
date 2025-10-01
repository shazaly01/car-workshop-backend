<?php

namespace App\Http\Requests;

use App\Models\Quotation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // استخراج عرض السعر من الرابط
        /** @var Quotation $quotation */
        $quotation = $this->route('quotation');

        // اسمح بالطلب فقط إذا كان المستخدم يملك صلاحية "تحديث" عرض السعر هذا
        return $this->user()->can('update', $quotation);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // قواعد التحقق لعملية التحديث
        // نستخدم 'sometimes' للسماح بتحديث جزئي (مثلاً، تحديث الملاحظات فقط)
        // لكن إذا تم إرسال مصفوفة البنود، فيجب أن تكون كاملة وصحيحة
       return [
            'notes' => 'sometimes|nullable|string|max:1000',

            // اجعل 'items' مطلوبة فقط إذا لم يتم إرسال 'status'
            'items' => 'required_without:status|array|min:1',
            'items.*.catalog_item_id' => 'required_with:items|exists:catalog_items,id',
            'items.*.quantity' => 'required_with:items|numeric|min:0.1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',

            // أضف قاعدة التحقق الجديدة للحالة
            'status' => [
                'sometimes',
                'string',
                Rule::in(['approved', 'rejected']), // اسمح بهاتين القيمتين فقط
            ],
        ];
    }
}
