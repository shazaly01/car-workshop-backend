<?php

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\WorkOrder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DiagnosisAndStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $receptionist;
    private User $technician;
    private WorkOrder $workOrder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->withRole('admin')->create();
        $this->receptionist = User::factory()->withRole('receptionist')->create();
        $this->technician = User::factory()->withRole('technician')->create();

        // إنشاء أمر عمل جاهز للتشخيص
        $this->workOrder = WorkOrder::factory()->create(['status' => 'pending_diagnosis']);
    }

    // --- اختبارات التشخيص (Diagnosis) ---

    /** @test */
    public function technician_can_add_diagnosis_to_work_order(): void
    {
        $diagnosisData = [
            'manual_inspection_results' => 'Brake pads are worn out.',
            'proposed_repairs' => 'Replace front brake pads.',
        ];

        $this->actingAs($this->technician, 'sanctum');

        $response = $this->postJson("/api/work-orders/{$this->workOrder->id}/diagnoses", $diagnosisData);

        $response->assertCreated();
        $this->assertDatabaseHas('diagnoses', [
            'work_order_id' => $this->workOrder->id,
            'technician_id' => $this->technician->id,
        ]);

        // تحقق من أن حالة أمر العمل قد تغيرت
        $this->assertEquals('pending_quote_approval', $this->workOrder->fresh()->status);
    }

    /** @test */
    public function receptionist_is_forbidden_from_adding_diagnosis(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');
        $this->postJson("/api/work-orders/{$this->workOrder->id}/diagnoses", [])->assertForbidden();
    }

    // --- اختبارات تغيير الحالة (Status Change) ---

    /** @test */
    public function authorized_users_can_change_work_order_status(): void
    {
        // 1. اختبار الفني
        $this->actingAs($this->technician, 'sanctum');
        $this->putJson("/api/work-orders/{$this->workOrder->id}/status", ['status' => 'in_progress'])
            ->assertOk();
        $this->assertEquals('in_progress', $this->workOrder->fresh()->status);

        // 2. اختبار موظف الاستقبال
        $this->actingAs($this->receptionist, 'sanctum');
        $this->putJson("/api/work-orders/{$this->workOrder->id}/status", ['status' => 'completed'])
            ->assertOk();
        $this->assertEquals('completed', $this->workOrder->fresh()->status);
    }

    /** @test */
    public function it_requires_a_valid_status(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');
        $this->putJson("/api/work-orders/{$this->workOrder->id}/status", ['status' => 'invalid_status'])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('status');
    }
}
