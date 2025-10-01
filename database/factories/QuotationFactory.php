<?php

namespace Database\Factories;

use App\Models\QuotationItem;
use App\Models\WorkOrder;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'number' => 'Q-' . date('Y') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'issue_date' => now(),
            'expiry_date' => now()->addDays(15),
            'status' => 'pending',
            'notes' => $this->faker->optional()->sentence(),
            // المجاميع سيتم حسابها لاحقًا
            'subtotal' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
        ];
    }

    /**
     * --- الجزء الجديد والمهم ---
     *
     * حالة لإنشاء عرض سعر مع عدد محدد من البنود.
     */
    public function withItems(int $count = 3): Factory
    {
        return $this->afterCreating(function (Quotation $quotation) use ($count) {
            // أنشئ بنودًا مرتبطة بعرض السعر هذا
            $items = QuotationItem::factory()->count($count)->create([
                'quotation_id' => $quotation->id,
            ]);

            // بعد إنشاء البنود، قم بتحديث مجاميع عرض السعر
            $subtotal = $items->sum('total_price');
            $taxAmount = $subtotal * 0.15; // افترضنا ضريبة 15%
            $totalAmount = $subtotal + $taxAmount;

            $quotation->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);
        });
    }
}
