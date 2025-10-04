<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // إزالة التعليق وتفعيله
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->pluck('id'); // إرجاع مصفوفة بـ IDs الصلاحيات
            }),
        ];
    }
}
