<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  public function authorize(): bool
{
    return true;
}

public function rules(): array
{
    // في التحديث، قد لا نسمح بتغيير العميل أو السيارة
    return [
        'client_complaint' => 'sometimes|required|string|min:10',
        'initial_inspection_notes' => 'sometimes|nullable|string',
        // لا نسمح بتغيير الحالة من هنا، سيكون لها متحكم خاص بها
    ];
}

}
