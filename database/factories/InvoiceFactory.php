<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\InvoiceItem;
use App\Models\WorkOrder;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'client_id' => function (array $attributes) {
                // احصل على العميل من أمر العمل لضمان التناسق
                return WorkOrder::find($attributes['work_order_id'])->client_id;
            },
            'number' => 'INV-' . date('Y') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'unpaid',
            'subtotal' => 0,
            'tax_percentage' => 15.00,
            'tax_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
        ];
    }

    public function withItems(int $count = 3): Factory
    {
        return $this->afterCreating(function (Invoice $invoice) use ($count) {
            $items = InvoiceItem::factory()->count($count)->create([
                'invoice_id' => $invoice->id,
            ]);

            $subtotal = $items->sum('total_price');
            $taxAmount = $subtotal * ($invoice->tax_percentage / 100);
            $totalAmount = $subtotal + $taxAmount;

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);
        });
    }
}
