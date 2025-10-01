<?php

namespace Tests\Feature\API;

use App\Models\CatalogItem;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CatalogItemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $receptionist;
    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        // إنشاء المستخدمين بعد تشغيل الـ Seeder لضمان وجود الأدوار
        $this->admin = User::factory()->create()->assignRole('admin');
        $this->receptionist = User::factory()->create()->assignRole('receptionist');
        $this->technician = User::factory()->create()->assignRole('technician');
    }

    // --- اختبارات الصلاحيات (Permissions) ---

    /** @test */
    public function guest_cannot_manage_catalog_items(): void
    {
        $this->getJson('/api/catalog-items')->assertUnauthorized();
        $this->postJson('/api/catalog-items', [])->assertUnauthorized();
    }

    /**
     * هذا الاختبار يستخدم dataProvider لاختبار جميع المستخدمين غير المصرح لهم.
     * @test
     * @dataProvider unauthorizedUsersProvider
     */
    public function unauthorized_users_cannot_manage_catalog_items(string $role): void
    {
        $user = $this->{$role}; // الوصول إلى المستخدم (receptionist أو technician)
        $item = CatalogItem::factory()->create();

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/catalog-items')->assertForbidden();
        $this->getJson("/api/catalog-items/{$item->id}")->assertForbidden();
        $this->postJson('/api/catalog-items', [])->assertForbidden();
        $this->putJson("/api/catalog-items/{$item->id}", [])->assertForbidden();
        $this->deleteJson("/api/catalog-items/{$item->id}")->assertForbidden();
    }

    /**
     * مزود البيانات للمستخدمين غير المصرح لهم.
     */
    public static function unauthorizedUsersProvider(): array
    {
        return [
            'Receptionist User' => ['receptionist'],
            'Technician User' => ['technician'],
        ];
    }

    /** @test */
    public function admin_can_manage_catalog_items(): void
    {
        $item = CatalogItem::factory()->create();

        $createData = [
            'sku' => 'SRV-001', // تم إضافة الحقل المطلوب
            'name' => 'New Service Item',
            'type' => 'service',
            'unit_price' => 120.50,
            'is_active' => true,
        ];

        // تم تعديل بيانات التحديث لتكون كائنًا كاملاً
        $updateData = [
            'sku' => $item->sku,
            'name' => 'Updated Service Item Name', // القيمة الجديدة
            'type' => $item->type,
            'unit_price' => $item->unit_price,
            'is_active' => $item->is_active,
        ];

        $this->actingAs($this->admin, 'sanctum');

        // Test GET all and GET one
        $this->getJson('/api/catalog-items')->assertOk();
        $this->getJson("/api/catalog-items/{$item->id}")->assertOk();

        // Test POST
        $this->postJson('/api/catalog-items', $createData)->assertCreated();

        // Test PUT
        $this->putJson("/api/catalog-items/{$item->id}", $updateData)->assertOk();
        $this->assertDatabaseHas('catalog_items', [
            'id' => $item->id,
            'name' => 'Updated Service Item Name'
        ]);

        // Test DELETE
        $this->deleteJson("/api/catalog-items/{$item->id}")->assertNoContent();
        $this->assertSoftDeleted('catalog_items', ['id' => $item->id]);
    }

    // --- اختبارات التحقق من صحة البيانات (Validation) ---

    /** @test */
    public function it_requires_name_type_and_unit_price_to_create_a_catalog_item(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        // سنضيف sku هنا لأننا اكتشفنا أنه مطلوب
        $this->postJson('/api/catalog-items', ['sku' => 'SKU-123'])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('name')
            ->assertJsonValidationErrorFor('type')
            ->assertJsonValidationErrorFor('unit_price');
    }

    /** @test */
    public function type_must_be_either_service_or_part(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $data = CatalogItem::factory()->make(['type' => 'invalid_type'])->toArray();

        $this->postJson('/api/catalog-items', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('type');
    }
}
