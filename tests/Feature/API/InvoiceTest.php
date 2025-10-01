<?php

namespace Tests\Feature\API;

use App\Models\Quotation;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Invoice;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $receptionist;
    private User $technician;
    private WorkOrder $readyWorkOrder;
    private WorkOrder $pendingWorkOrder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->receptionist = User::factory()->create()->assignRole('receptionist');
        $this->technician = User::factory()->create()->assignRole('technician');

        $this->readyWorkOrder = WorkOrder::factory()->create(['status' => 'ready_for_delivery']);
        Quotation::factory()->create([
            'work_order_id' => $this->readyWorkOrder->id,
            'status' => 'approved',
            'total_amount' => 1150,
        ]);

        $this->pendingWorkOrder = WorkOrder::factory()->create(['status' => 'in_progress']);
    }

    /** @test */
    public function guest_cannot_create_invoices(): void
    {
        $this->postJson("/api/work-orders/{$this->readyWorkOrder->id}/invoices")
             ->assertUnauthorized();
    }

    /** @test */
    public function technician_is_forbidden_from_creating_invoices(): void
    {
        $this->actingAs($this->technician, 'sanctum');
        $this->postJson("/api/work-orders/{$this->readyWorkOrder->id}/invoices")
             ->assertForbidden();
    }

    /** @test */
    public function receptionist_can_create_invoice_from_a_ready_work_order(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');
        $response = $this->postJson("/api/work-orders/{$this->readyWorkOrder->id}/invoices");

        $response->assertCreated();

        // استخدام مقارنة آمنة للأنواع الرقمية
        $this->assertSame(
            number_format(1150, 2, '.', ''),
            number_format($response->json('data.total_amount'), 2, '.', '')
        );

        $this->assertDatabaseHas('invoices', [
            'work_order_id' => $this->readyWorkOrder->id,
            'total_amount' => 1150,
        ]);
    }

    /** @test */
    public function it_cannot_create_invoice_if_work_order_is_not_in_correct_status(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');
        $this->postJson("/api/work-orders/{$this->pendingWorkOrder->id}/invoices")
             ->assertStatus(409);
    }

    /** @test */
    public function it_cannot_create_invoice_if_no_approved_quotation_exists(): void
    {
        $workOrderWithoutQuote = WorkOrder::factory()->create(['status' => 'ready_for_delivery']);
        $this->actingAs($this->receptionist, 'sanctum');
        $this->postJson("/api/work-orders/{$workOrderWithoutQuote->id}/invoices")
             ->assertStatus(422);
    }


    // أضف هذه الاختبارات الجديدة داخل كلاس InvoiceTest

    /** @test */
    public function technician_is_forbidden_from_voiding_an_invoice(): void
    {
        // 1. إنشاء فاتورة غير مدفوعة
        $invoice = Invoice::factory()->create(['status' => 'unpaid']);

        // 2. محاولة الإلغاء من قبل الفني
        $this->actingAs($this->technician, 'sanctum');
        // سنفترض أن المسار هو DELETE /api/invoices/{invoice}
        $response = $this->deleteJson("/api/invoices/{$invoice->id}");

        // 3. التحقق من المنع
        $response->assertForbidden();
    }

    /** @test */
    public function receptionist_can_void_an_unpaid_invoice(): void
    {
        // 1. إنشاء فاتورة غير مدفوعة
        $invoice = Invoice::factory()->create(['status' => 'unpaid']);

        // 2. محاولة الإلغاء من قبل موظف الاستقبال
        $this->actingAs($this->receptionist, 'sanctum');
        $response = $this->deleteJson("/api/invoices/{$invoice->id}");

        // 3. التحقق من النجاح
        $response->assertNoContent(); // 204 No Content هي الاستجابة المناسبة لعملية حذف ناجحة

        // 4. التحقق من أن الحالة تغيرت في قاعدة البيانات
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'voided',
        ]);
    }

    /** @test */
    public function it_prevents_voiding_a_paid_invoice(): void
    {
        // 1. إنشاء فاتورة مدفوعة جزئيًا
        $invoice = Invoice::factory()->create(['status' => 'partially_paid']);

        // 2. محاولة الإلغاء
        $this->actingAs($this->receptionist, 'sanctum');
        $response = $this->deleteJson("/api/invoices/{$invoice->id}");

        // 3. التحقق من الفشل مع رسالة خطأ مناسبة
        $response->assertStatus(409); // 409 Conflict هو رمز مناسب لهذه الحالة
        $response->assertJsonFragment(['message' => 'لا يمكن إلغاء فاتورة مدفوعة.']);
    }

}
