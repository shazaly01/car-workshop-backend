<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
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
        $vehicleId = $this->route('vehicle')->id;

        return [
            'client_id' => 'required|exists:clients,id',
            'vin' => 'required|string|unique:vehicles,vin,' . $vehicleId,
            'plate_number' => 'required|string|unique:vehicles,plate_number,' . $vehicleId,
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|digits:4|integer|min:1900',
            'color' => 'nullable|string|max:50',
            'mileage' => 'nullable|integer|min:0',
        ];
    }

}
