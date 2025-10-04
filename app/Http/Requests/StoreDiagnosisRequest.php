<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\WorkOrder;
use App\Models\Diagnosis;

class StoreDiagnosisRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   public function authorize(): bool
{
           // --- هذا هو التعديل ---
        // استخراج أمر العمل من الرابط
        /** @var WorkOrder $workOrder */
        $workOrder = $this->route('work_order');

        // اسمح بالطلب فقط إذا كان المستخدم يملك صلاحية "إضافة تشخيص"
        return $this->user()->can('create', [Diagnosis::class, $workOrder]);
}

public function rules(): array
{
    return [
      //  'work_order_id' => 'required|exists:work_orders,id|unique:diagnoses,work_order_id', // يجب أن يكون لأمر عمل موجود، ولم يتم تشخيصه من قبل
        'technician_id' => 'nullable|exists:users,id', // الفني يجب أن يكون مستخدماً مسجلاً
        'obd_codes' => 'nullable|array', // يمكن أن يكون مصفوفة من الأكواد
        'obd_codes.*' => 'string|max:10', // كل كود داخل المصفوفة يجب أن يكون نصاً
        'manual_inspection_results' => 'required|string|min:5',
        'proposed_repairs' => 'nullable|string',
    ];
}

}
