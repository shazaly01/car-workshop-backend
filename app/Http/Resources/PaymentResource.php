<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'payment_date' => $this->payment_date->toFormattedDateString(),
            'payment_method' => $this->payment_method,
            'transaction_reference' => $this->transaction_reference,
            'notes' => $this->notes,
            'received_by' => new UserResource($this->whenLoaded('receivedBy')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
