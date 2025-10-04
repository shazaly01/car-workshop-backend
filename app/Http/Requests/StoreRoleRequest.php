<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],

            // [تعديل هنا]
            // 'sometimes' تعني: إذا كان حقل 'permissions' موجودًا، فيجب أن يكون مصفوفة.
            // إذا لم يكن موجودًا، تجاهل قواعد التحقق هذه.
            'permissions' => ['sometimes', 'array'],

            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ];
    }
}
