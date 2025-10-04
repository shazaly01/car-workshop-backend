<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Diagnosis; // <-- استيراد مودل التشخيص

class UpdateDiagnosisRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // استخراج كائن التشخيص من المسار
        /** @var Diagnosis $diagnosis */
        $diagnosis = $this->route('diagnosis');

        // اسمح بالطلب فقط إذا كان المستخدم لديه صلاحية "تحديث" هذا التشخيص المحدد
        // (سوف تحتاج إلى تعريف هذه الصلاحية في DiagnosisPolicy)
        return $this->user()->can('update', $diagnosis);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // القواعد مشابهة لـ StoreDiagnosisRequest ولكن بدون التحقق من التفرد
        return [
            'obd_codes' => 'nullable|array',
            'obd_codes.*' => 'string|max:10',
            'manual_inspection_results' => 'required|string|min:5',
            'proposed_repairs' => 'nullable|string',
        ];
    }
}
