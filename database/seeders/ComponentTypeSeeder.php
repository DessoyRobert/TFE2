<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ComponentType;

class ComponentTypeSeeder extends Seeder
{
public function run()
{
    $types = [
        'cpu',
        'gpu',
        'ram',
        'storage',
        'psu',
        'cooler',
        'case',
        'motherboard',
    ];
    foreach ($types as $type) {
        \App\Models\ComponentType::create(['name' => $type]);
    }
}

}

