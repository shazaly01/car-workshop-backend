<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'type' => $this->client_type,
            'registered_at' => $this->created_at->toFormattedDateString(), // تنسيق التاريخ
            // تحميل العلاقات فقط إذا كانت موجودة بالفعل
            'vehicles' => VehicleResource::collection($this->whenLoaded('vehicles')),
            'work_orders_count' => $this->whenCounted('workOrders'), // مثال: إضافة عدد أوامر العمل
        ];
    }
}
