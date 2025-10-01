<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'issue_date' => $this->issue_date,
            'expiry_date' => $this->expiry_date,
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'total_amount' => (float) $this->total_amount,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toDateTimeString(),
            // تضمين البنود باستخدام المورد الذي أنشأناه للتو
             'items' => QuotationItemResource::collection($this->whenLoaded('items')),

            // يمكنك أيضًا عرض بيانات أمر العمل المرتبط به
            'work_order' => new WorkOrderResource($this->whenLoaded('workOrder')),
        ];
    }
}
