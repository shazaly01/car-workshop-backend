<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
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
    return [
        'client_id' => 'required|exists:clients,id', // التأكد من أن العميل المالك موجود
        'vin' => 'required|string|unique:vehicles,vin', // رقم الهيكل فريد
        'plate_number' => 'required|string|unique:vehicles,plate_number', // رقم اللوحة فريد
        'make' => 'required|string|max:100',
        'model' => 'required|string|max:100',
        'year' => 'required|digits:4|integer|min:1900',
        'color' => 'nullable|string|max:50',
        'mileage' => 'nullable|integer|min:0',
    ];
}

}
