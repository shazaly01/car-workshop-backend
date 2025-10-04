<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCatalogItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // التحقق من أن المستخدم يملك صلاحية إدارة الكتالوج
        return $this->user()->can('manage catalog');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // الحصول على ID الصنف الذي يتم تحديثه من المسار
        $catalogItemId = $this->route('catalog_item')->id;

        return [
            // عند التحقق من تفرد كود الصنف، يجب أن نتجاهل الصنف الحالي نفسه
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('catalog_items', 'sku')->ignore($catalogItemId),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:part,service',
            'unit_price' => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
