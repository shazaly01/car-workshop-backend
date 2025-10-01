<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   public function authorize(): bool
{
    return true; // نسمح لأي مستخدم مسجل بالإنشاء حالياً
}

public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|unique:clients,phone', // يجب أن يكون فريداً في جدول العملاء
        'email' => 'nullable|email|unique:clients,email', // اختياري، ولكن يجب أن يكون فريداً إذا وُجد
        'address' => 'nullable|string|max:500',
        'client_type' => 'required|in:individual,company', // يجب أن يكون إحدى هاتين القيمتين
    ];
}

}
