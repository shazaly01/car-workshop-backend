<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'catalog_item_id' => $this->catalog_item_id,
            'description' => $this->description,
            'type' => $this->type,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'total_price' => (float) $this->total_price,
            'catalog_item' => new CatalogItemResource($this->whenLoaded('catalogItem')),
        ];
    }
}
