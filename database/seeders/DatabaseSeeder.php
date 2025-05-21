<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
public function run()
{
    $this->call([
        ComponentSeeder::class,
        CpuSeeder::class,
        GpuSeeder::class,
        RamSeeder::class,
        MotherboardSeeder::class,
        StorageSeeder::class,
        PsuSeeder::class,
        CoolerSeeder::class,
        CaseSeeder::class,
    ]);
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
