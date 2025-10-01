<?php

namespace Database\Factories;

use App\Models\CatalogItem;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuotationItem>
 */
class QuotationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // ننشئ بند كتالوج وهمي للحصول على بيانات واقعية
        $catalogItem = CatalogItem::factory()->create();
        $quantity = $this->faker->numberBetween(1, 3);
        $unitPrice = $catalogItem->unit_price;

        return [
            'quotation_id' => Quotation::factory(),
            'catalog_item_id' => $catalogItem->id,
            'description' => $catalogItem->name,
            'type' => $catalogItem->type,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
        ];
    }
}
