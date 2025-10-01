<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // بما أننا نستخدم middleware الصلاحيات على المسار،
        // يمكننا ترك هذا true. الـ middleware سيقوم بالتحقق أولاً.
        // أو يمكننا إضافة تحقق صريح هنا لمزيد من الأمان:
        return $this->user()->can('manage catalog');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sku' => 'required|string|max:50|unique:catalog_items,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:part,service',
            'unit_price' => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
