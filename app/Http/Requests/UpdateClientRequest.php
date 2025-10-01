<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
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
    $clientId = $this->route('client')->id; // الحصول على ID العميل من الرابط

    return [
        'name' => 'required|string|max:255',
        // عند التحديث، يجب أن نتجاهل السجل الحالي عند التحقق من التفرد (unique)
        'phone' => 'required|string|unique:clients,phone,' . $clientId,
        'email' => 'nullable|email|unique:clients,email,' . $clientId,
        'address' => 'nullable|string|max:500',
        'client_type' => 'required|in:individual,company',
    ];
}

}
