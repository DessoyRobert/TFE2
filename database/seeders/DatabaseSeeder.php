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
        ComponentTypeSeeder::class,
        CategorySeeder::class,
        BrandSeeder::class,
        //ComponentSeeder::class,
        CpuSeeder::class,
        GpuSeeder::class,
        RamSeeder::class,
        MotherboardSeeder::class,
        StorageSeeder::class,
        PsuSeeder::class,
        CoolerSeeder::class,
        CaseSeeder::class,
        CompatibilityRuleSeeder::class,
           ]);
    // Create a default user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
