<?php

namespace Database\Factories;

use App\Models\CatalogItem;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    public function definition(): array
    {
        $catalogItem = CatalogItem::factory()->create();
        $quantity = $this->faker->numberBetween(1, 3);
        $unitPrice = $catalogItem->unit_price;

        return [
            'invoice_id' => Invoice::factory(),
            'catalog_item_id' => $catalogItem->id,
            'description' => $catalogItem->name,
            'type' => $catalogItem->type,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
        ];
    }
}
