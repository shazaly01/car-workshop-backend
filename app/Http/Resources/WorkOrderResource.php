<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
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
            'number' => $this->number,
            'status' => $this->status,
            'status_translated' => $this->getTranslatedStatus(), // حقل إضافي لترجمة الحالة
            'client_complaint' => $this->client_complaint,
            'initial_inspection_notes' => $this->initial_inspection_notes,
            'created_at' => $this->created_at->toDateTimeString(),

            // --- العلاقات المضمنة ---
            // استخدام الموارد التي أنشأناها سابقاً
            'client' => new ClientResource($this->whenLoaded('client')),
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'created_by' => new UserResource($this->whenLoaded('createdBy')), // سنحتاج لإنشاء UserResource

            // --- علاقات أخرى يمكن تحميلها لاحقاً ---
            'diagnosis' => new DiagnosisResource($this->whenLoaded('diagnosis')),
            'quotations' => QuotationResource::collection($this->whenLoaded('quotations')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
        ];
    }

    /**
     * دالة مساعدة لترجمة الحالات
     */
    protected function getTranslatedStatus(): string
    {
        $statuses = [
            'pending_diagnosis' => 'بانتظار التشخيص',
            'diagnosing' => 'جاري التشخيص',
            'pending_quote_approval' => 'بانتظار موافقة عرض السعر',
            'quote_approved' => 'تمت الموافقة على عرض السعر',
            'quote_rejected' => 'تم رفض عرض السعر',
            'in_progress' => 'قيد الإصلاح',
            'pending_parts' => 'بانتظار قطع غيار',
            'quality_check' => 'فحص الجودة',
            'ready_for_delivery' => 'جاهز للتسليم',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}
