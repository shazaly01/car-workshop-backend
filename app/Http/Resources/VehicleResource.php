<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vin' => $this->vin,
            'plate_number' => $this->plate_number,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'mileage' => $this->mileage,
            'created_at' => $this->created_at->toIso8601String(),

            // تضمين بيانات العميل المالك (إذا تم تحميلها)
            // هذا يوضح كيف يمكن تضمين علاقة داخل مورد آخر
            'owner' => new ClientResource($this->whenLoaded('client')),
        ];
    }
}
