<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ComponentType;

class ComponentTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('component_types')->insert([
            ['name' => 'cpu'],
            ['name' => 'gpu'],
            ['name' => 'ram'],
            ['name' => 'motherboard'],
            ['name' => 'storage'],
            ['name' => 'psu'],
            ['name' => 'cooler'],
            ['name' => 'case_model'],
        ]);
    }
}

