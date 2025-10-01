<?php

namespace App\Http\Requests;

use App\Models\Vehicle; // 1. استيراد نموذج السيارة
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'client_complaint' => 'required|string|min:10',
            'initial_inspection_notes' => 'nullable|string',
        ];
    }

    /**
     * 2. إضافة هذه الدالة الجديدة
     * Configure the validator instance.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                // نبحث عن السيارة التي تم إرسالها في الطلب
                $vehicle = Vehicle::find($this->input('vehicle_id'));

                // إذا وجدنا السيارة، وتأكدنا أن client_id الخاص بها
                // لا يطابق الـ client_id الذي تم إرساله في الطلب
                if ($vehicle && $vehicle->client_id != $this->input('client_id')) {
                    // نضيف خطأً مخصصًا إلى حقل vehicle_id
                    $validator->errors()->add(
                        'vehicle_id',
                        'هذه السيارة لا تنتمي للعميل المحدد.' // The selected vehicle does not belong to the specified client.
                    );
                }
            }
        ];
    }
}
