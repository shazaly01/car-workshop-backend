<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Vehicle;

class ClientsAndVehiclesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // إنشاء 20 عميل
        Client::factory(20)->create()->each(function ($client) {
            // لكل عميل، قم بإنشاء مركبة واحدة أو مركبتين
            Vehicle::factory(rand(1, 2))->create([
                'client_id' => $client->id,
            ]);
        });
    }
}

