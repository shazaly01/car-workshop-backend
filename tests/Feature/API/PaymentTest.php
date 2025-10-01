<?php

namespace Tests\Feature\API;

use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $receptionist;
    private User $technician;
    private Invoice $unpaidInvoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->receptionist = User::factory()->create()->assignRole('receptionist');
        $this->technician = User::factory()->create()->assignRole('technician');

        $this->unpaidInvoice = Invoice::factory()->create([
            'status' => 'unpaid',
            'total_amount' => 1000,
            'paid_amount' => 0,
        ]);
    }

    /** @test */
    public function guest_cannot_add_payment(): void
    {
        $this->postJson("/api/invoices/{$this->unpaidInvoice->id}/payments", [])
             ->assertUnauthorized();
    }

    /** @test */
    public function technician_is_forbidden_from_adding_payment(): void
    {
        $this->actingAs($this->technician, 'sanctum');
        // أرسل بيانات صالحة لتجاوز مرحلة التحقق من القواعد والوصول إلى التحقق من الصلاحيات
        $paymentData = [
            'amount' => 100,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ];
        $this->postJson("/api/invoices/{$this->unpaidInvoice->id}/payments", $paymentData)
             ->assertForbidden();
    }

    /** @test */
    public function receptionist_can_add_a_partial_payment(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');

        $paymentData = [
            'amount' => 400,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ];

        $response = $this->postJson("/api/invoices/{$this->unpaidInvoice->id}/payments", $paymentData);

        // استخدام مقارنة آمنة للأنواع الرقمية
        $response->assertCreated();
        $this->assertSame(
            number_format(400, 2, '.', ''),
            number_format($response->json('data.amount'), 2, '.', '')
        );

        $this->assertDatabaseHas('invoices', [
            'id' => $this->unpaidInvoice->id,
            'status' => 'partially_paid',
            'paid_amount' => 400,
        ]);
    }

    /** @test */
    public function receptionist_can_add_a_full_payment(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');

        $paymentData = [
            'amount' => 1000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'card',
        ];

        $this->postJson("/api/invoices/{$this->unpaidInvoice->id}/payments", $paymentData)
             ->assertCreated();

        $this->assertDatabaseHas('invoices', [
            'id' => $this->unpaidInvoice->id,
            'status' => 'paid',
            'paid_amount' => 1000,
        ]);
    }

    /** @test */
    public function it_prevents_overpayment(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');

        $paymentData = [
            'amount' => 1001,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ];

        $response = $this->postJson("/api/invoices/{$this->unpaidInvoice->id}/payments", $paymentData);

        // تعديل نص الرسالة ليتطابق مع الرسالة الفعلية
        $response->assertStatus(422)
                 ->assertJson([
                     'message' => 'مبلغ الدفعة أكبر من المبلغ المتبقي على الفاتورة. المتبقي: 1000'
                 ]);
    }

    /** @test */
    public function it_requires_valid_data_to_add_payment(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');

        $this->postJson("/api/invoices/{$this->unpaidInvoice->id}/payments", [])
             ->assertStatus(422)
             ->assertJsonValidationErrorFor('amount')
             ->assertJsonValidationErrorFor('payment_date')
             ->assertJsonValidationErrorFor('payment_method');
    }
}
