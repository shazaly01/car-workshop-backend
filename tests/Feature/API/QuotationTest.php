<?php

namespace Tests\Feature\API;

use App\Models\CatalogItem;
use App\Models\User;
use App\Models\WorkOrder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QuotationTest extends TestCase
{
    use RefreshDatabase;

    private User $receptionist;
    private User $technician;
    private WorkOrder $workOrder;
    private CatalogItem $serviceItem;
    private CatalogItem $partItem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->receptionist = User::factory()->withRole('receptionist')->create();
        $this->technician = User::factory()->withRole('technician')->create();

        // أمر عمل جاهز لإنشاء عرض سعر
        $this->workOrder = WorkOrder::factory()->create(['status' => 'pending_quote_approval']);

        // بنود كتالوج جاهزة للاستخدام
        $this->serviceItem = CatalogItem::factory()->create(['type' => 'service', 'unit_price' => 150]);
        $this->partItem = CatalogItem::factory()->create(['type' => 'part', 'unit_price' => 200]);
    }

    /** @test */
    public function receptionist_can_create_and_update_quotation(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');

        // --- 1. اختبار الإنشاء (Create) ---
        $quotationData = [
            'work_order_id' => $this->workOrder->id,
            'issue_date' => now()->toDateString(),
            'expiry_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Initial quotation notes.',
            'items' => [
                [
                    'catalog_item_id' => $this->serviceItem->id,
                    'quantity' => 1,
                    'unit_price' => 150,
                ],
                [
                    'catalog_item_id' => $this->partItem->id,
                    'quantity' => 2,
                    'unit_price' => 200,
                ],
            ],
        ];

        $response = $this->postJson("/api/work-orders/{$this->workOrder->id}/quotations", $quotationData);
        $response->assertCreated();

        // تحقق من أن المجاميع تم حسابها بشكل صحيح (150 + 2*200 = 550)
        $response->assertJsonPath('data.subtotal', 550);

        // --- 2. اختبار التحديث (Update) ---
        $quotation = $this->workOrder->quotation; // احصل على عرض السعر الذي تم إنشاؤه
        $updateData = [
            'notes' => 'Updated quotation notes.',
            'items' => [
                [
                    'catalog_item_id' => $this->serviceItem->id,
                    'quantity' => 1,
                    'unit_price' => 160, // تم تغيير السعر
                ],
            ],
        ];

        $updateResponse = $this->putJson("/api/quotations/{$quotation->id}", $updateData);
        $updateResponse->assertOk();
        $updateResponse->assertJsonPath('data.notes', 'Updated quotation notes.');
        $updateResponse->assertJsonPath('data.subtotal', 160); // تحقق من أن المجموع تم تحديثه
    }

    /** @test */
    public function technician_is_forbidden_from_managing_quotations(): void
    {
        $this->actingAs($this->technician, 'sanctum');
        $quotation = \App\Models\Quotation::factory()->create(['work_order_id' => $this->workOrder->id]);

        $this->postJson("/api/work-orders/{$this->workOrder->id}/quotations", [])->assertForbidden();
        $this->putJson("/api/quotations/{$quotation->id}", [])->assertForbidden();
    }



    // أضف هذا الاختبار الجديد داخل كلاس QuotationTest

    /** @test */
    public function receptionist_can_reject_a_quotation_which_cancels_the_work_order(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');

        // 1. إنشاء عرض سعر معلق مرتبط بأمر العمل
        $quotation = \App\Models\Quotation::factory()->create([
            'work_order_id' => $this->workOrder->id,
            'status' => 'pending',
        ]);

        // 2. إرسال طلب لتحديث الحالة إلى 'rejected'
        // سنفترض أن المسار هو PUT /api/quotations/{quotation}
        // وسنرسل الحالة الجديدة في جسم الطلب
        $updateData = ['status' => 'rejected'];
        $response = $this->putJson("/api/quotations/{$quotation->id}", $updateData);

        // 3. التحقق من نجاح الطلب
        $response->assertOk();

        // 4. التحقق من أن حالة عرض السعر تغيرت في قاعدة البيانات
        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => 'rejected',
        ]);

        // 5. التحقق من أن حالة أمر العمل المرتبط تغيرت تلقائيًا
        $this->assertDatabaseHas('work_orders', [
            'id' => $this->workOrder->id,
            'status' => 'cancelled',
        ]);
    }

}
