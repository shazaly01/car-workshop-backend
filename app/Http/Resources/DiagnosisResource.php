<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiagnosisResource extends JsonResource
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
            'obd_codes' => $this->obd_codes, // سيتم تحويله لمصفوفة تلقائياً بفضل $casts في النموذج
            'manual_inspection_results' => $this->manual_inspection_results,
            'proposed_repairs' => $this->proposed_repairs,
            'diagnosed_at' => $this->created_at->toDateTimeString(),
            'technician' => new UserResource($this->whenLoaded('technician')),
        ];
    }
}
