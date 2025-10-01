<?php

namespace Tests\Feature\API;

use App\Models\Client;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $receptionist;
    private User $technician;
    private Client $client;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->withRole('admin')->create();
        $this->receptionist = User::factory()->withRole('receptionist')->create();
        $this->technician = User::factory()->withRole('technician')->create();

        $this->client = Client::factory()->create();
        $this->vehicle = Vehicle::factory()->create(['client_id' => $this->client->id]);
    }

    /** @test */
    public function guest_is_forbidden_from_accessing_work_orders(): void
    {
        $this->getJson('/api/work-orders')->assertUnauthorized();
        $this->postJson('/api/work-orders', [])->assertUnauthorized();
    }

    /** @test */
    public function receptionist_can_manage_work_orders(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
        ]);

        $createData = [
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'client_complaint' => 'New client complaint.',
        ];

        $updateData = ['client_complaint' => 'Updated client complaint.'];

        $this->actingAs($this->receptionist, 'sanctum');

        $this->getJson('/api/work-orders')->assertOk();
        $this->getJson("/api/work-orders/{$workOrder->id}")->assertOk();
        $this->postJson('/api/work-orders', $createData)->assertCreated();
        $this->putJson("/api/work-orders/{$workOrder->id}", $updateData)->assertOk();
        $this->deleteJson("/api/work-orders/{$workOrder->id}")->assertNoContent();
    }

    /** @test */
    public function technician_has_limited_access_to_work_orders(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'client_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
        ]);

        $this->actingAs($this->technician, 'sanctum');

        // Should be able to view
        $this->getJson('/api/work-orders')->assertOk();
        $this->getJson("/api/work-orders/{$workOrder->id}")->assertOk();

        // Should be forbidden from creating, updating, or deleting
        $this->postJson('/api/work-orders', [])->assertForbidden();
        $this->putJson("/api/work-orders/{$workOrder->id}", [])->assertForbidden();
        $this->deleteJson("/api/work-orders/{$workOrder->id}")->assertForbidden();
    }

    /** @test */
    public function it_requires_client_vehicle_and_complaint_to_create_work_order(): void
    {
        $this->actingAs($this->receptionist, 'sanctum');

        $this->postJson('/api/work-orders', [])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('client_id')
            ->assertJsonValidationErrorFor('vehicle_id')
            ->assertJsonValidationErrorFor('client_complaint');
    }
}
