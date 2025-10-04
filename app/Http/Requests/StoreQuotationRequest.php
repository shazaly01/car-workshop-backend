<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Quotation; // <-- أضف هذا
use App\Models\WorkOrder; // <-- أضف هذا

class StoreQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  public function authorize(): bool
    {
        // --- أضف هذا المنطق ---
        /** @var WorkOrder $workOrder */
        $workOrder = $this->route('work_order');
        return $this->user()->can('create', [Quotation::class, $workOrder]);
    }

public function rules(): array
{
    return [
       // 'work_order_id' => 'required|exists:work_orders,id',
        'issue_date' => 'required|date',
        'expiry_date' => 'required|date|after_or_equal:issue_date', // تاريخ الانتهاء يجب أن يكون بعد تاريخ الإصدار
        'notes' => 'nullable|string',

        // التحقق من مصفوفة البنود
        'items' => 'required|array|min:1', // يجب أن يوجد على الأقل بند واحد
        'items.*.catalog_item_id' => 'nullable|exists:catalog_items,id',
        // 'items.*.description' => 'required|string|max:255',
        // 'items.*.type' => 'required|in:service,part',
        'items.*.quantity' => 'required|numeric|min:0.1',
        'items.*.unit_price' => 'required|numeric|min:0',
    ];
}

}
