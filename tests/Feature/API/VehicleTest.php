<?php

namespace Tests\Feature\API;

use App\Models\Client;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $receptionist;
    private User $technician;
    private Client $client;

    /**
     * الإعداد الأولي لكل اختبار في هذا الملف.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->withRole('admin')->create();
        $this->receptionist = User::factory()->withRole('receptionist')->create();
        $this->technician = User::factory()->withRole('technician')->create();

        // سنحتاج إلى عميل لربط السيارات به
        $this->client = Client::factory()->create();
    }

    // --- اختبارات الصلاحيات (Permissions) ---

    /** @test */
    public function guest_cannot_access_vehicles_endpoints(): void
    {
        $this->getJson('/api/vehicles')->assertUnauthorized();
        $this->postJson('/api/vehicles')->assertUnauthorized();
    }

    /** @test */
    public function technician_is_forbidden_from_managing_vehicles(): void
    {
        $vehicle = Vehicle::factory()->create(['client_id' => $this->client->id]);

        $this->actingAs($this->technician, 'sanctum');

        $this->getJson('/api/vehicles')->assertForbidden();
        $this->getJson("/api/vehicles/{$vehicle->id}")->assertForbidden();
        $this->postJson('/api/vehicles', [])->assertForbidden();
        $this->putJson("/api/vehicles/{$vehicle->id}", [])->assertForbidden();
        $this->deleteJson("/api/vehicles/{$vehicle->id}")->assertForbidden();
    }

    /**
     * هذا الاختبار يستخدم dataProvider لتجنب تكرار الكود للمدير وموظف الاستقبال.
     * @test
     * @dataProvider authorizedUsersProvider
     */
    public function authorized_users_can_manage_vehicles(string $role): void
    {
        $user = $this->{$role}; // الوصول إلى المستخدم (admin أو receptionist)
        $vehicle = Vehicle::factory()->create(['client_id' => $this->client->id]);

        // بيانات لإنشاء سيارة جديدة
        $createData = Vehicle::factory()->make(['client_id' => $this->client->id])->toArray();

        // بيانات لتحديث السيارة الحالية
        $updateData = [
            'client_id' => $this->client->id,
            'make' => 'Updated Make',
            'model' => 'Updated Model',
            'year' => 2025,
            'vin' => 'UPDATEDVIN123',
            'plate_number' => 'UPDATED-PLATE',
        ];

        $this->actingAs($user, 'sanctum');

        // Test GET all and GET one
        $this->getJson('/api/vehicles')->assertOk();
        $this->getJson("/api/vehicles/{$vehicle->id}")->assertOk();

        // Test POST
        $this->postJson('/api/vehicles', $createData)->assertCreated();

        // Test PUT
        $this->putJson("/api/vehicles/{$vehicle->id}", $updateData)->assertOk();

        // Test DELETE
        $this->deleteJson("/api/vehicles/{$vehicle->id}")->assertNoContent();
    }

    /**
     * مزود البيانات لاختبار المستخدمين المصرح لهم.
     */
    public static function authorizedUsersProvider(): array
    {
        return [
            'Admin User' => ['admin'],
            'Receptionist User' => ['receptionist'],
        ];
    }


    // --- اختبارات التحقق من صحة البيانات (Validation) ---

    /** @test */
    public function it_requires_vin_and_plate_number_to_create_a_vehicle(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');

        $response = $this->postJson('/api/vehicles', [
            'client_id' => $this->client->id,
            // بيانات ناقصة عمدًا
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('vin');
        $response->assertJsonValidationErrorFor('plate_number');
    }
}
