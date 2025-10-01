<?php

namespace Tests\Feature\API;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $receptionist;
    private User $technician;

    /**
     * الإعداد الأولي لكل اختبار في هذا الملف.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. تشغيل الـ Seeder لإنشاء الأدوار والصلاحيات
        $this->seed(RolesAndPermissionsSeeder::class);

        // 2. إنشاء مستخدمين لكل دور
        $this->admin = User::factory()->withRole('admin')->create();
        $this->receptionist = User::factory()->withRole('receptionist')->create();
        $this->technician = User::factory()->withRole('technician')->create();
    }

    // --- اختبارات الصلاحيات (Permissions) ---

    /** @test */
    public function guest_cannot_access_clients_endpoints(): void
    {
        $this->getJson('/api/clients')->assertUnauthorized();
        $this->postJson('/api/clients')->assertUnauthorized();
    }

    /** @test */
    public function technician_is_forbidden_from_accessing_clients_endpoints(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->technician, 'sanctum');

        $this->getJson('/api/clients')->assertForbidden();
        $this->getJson("/api/clients/{$client->id}")->assertForbidden();
        $this->postJson('/api/clients', [])->assertForbidden();
        $this->putJson("/api/clients/{$client->id}", [])->assertForbidden();
        $this->deleteJson("/api/clients/{$client->id}")->assertForbidden();
    }

    /** @test */
   public function receptionist_can_manage_clients(): void
{
    $client = Client::factory()->create();

    // بيانات جديدة ومحددة للتحديث
    $updateData = [
        'name' => 'Updated Name',
        'phone' => '123-456-7890', // رقم هاتف جديد ومضمون
        'email' => 'updated@email.com', // بريد إلكتروني جديد ومضمون
        'client_type' => 'company',
    ];

    $this->actingAs($this->receptionist, 'sanctum');

    $this->getJson('/api/clients')->assertOk();
    $this->getJson("/api/clients/{$client->id}")->assertOk();
    // اختبار الإنشاء يبقى كما هو
    $this->postJson('/api/clients', Client::factory()->make()->toArray())->assertCreated();
    // --- هذا هو التعديل ---
    $this->putJson("/api/clients/{$client->id}", $updateData)->assertOk();
    $this->deleteJson("/api/clients/{$client->id}")->assertNoContent();
}

    /** @test */
    public function admin_can_manage_clients(): void
{
    $client = Client::factory()->create();

    // بيانات جديدة ومحددة للتحديث
    $updateData = [
        'name' => 'Admin Updated Name',
        'phone' => '987-654-3210', // رقم هاتف جديد ومضمون
        'email' => 'admin.updated@email.com', // بريد إلكتروني جديد ومضمون
        'client_type' => 'individual',
    ];

    $this->actingAs($this->admin, 'sanctum');

    $this->getJson('/api/clients')->assertOk();
    $this->getJson("/api/clients/{$client->id}")->assertOk();
    // اختبار الإنشاء يبقى كما هو
    $this->postJson('/api/clients', Client::factory()->make()->toArray())->assertCreated();
    // --- هذا هو التعديل ---
    $this->putJson("/api/clients/{$client->id}", $updateData)->assertOk();
    $this->deleteJson("/api/clients/{$client->id}")->assertNoContent();
}

    // --- اختبارات التحقق من صحة البيانات (Validation) ---

    /** @test */
    public function it_requires_a_name_and_phone_to_create_a_client(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');

        $response = $this->postJson('/api/clients', [
            // بيانات ناقصة عمدًا
        ]);

        $response->assertStatus(422); // Unprocessable Entity
        $response->assertJsonValidationErrorFor('name');
        $response->assertJsonValidationErrorFor('phone');
    }

    /** @test */
    public function phone_and_email_must_be_unique(): void
    {
        // إنشاء عميل موجود مسبقًا
        $existingClient = Client::factory()->create();

        $this->actingAs($this->receptionist, 'sanctum');

        $response = $this->postJson('/api/clients', [
            'name' => 'New Client',
            'phone' => $existingClient->phone, // استخدام نفس رقم الهاتف
            'email' => $existingClient->email, // استخدام نفس البريد الإلكتروني
            'client_type' => 'individual',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('phone');
        $response->assertJsonValidationErrorFor('email');
    }
}
