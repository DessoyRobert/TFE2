<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\CaseModel;

class CaseSeeder extends Seeder
{
    public function run()
    {
        $component = Component::create([
            'name' => 'NZXT H510',
            'brand' => 'NZXT',
            'type' => 'case',
            'price' => 84.99,
            'img_url' => 'https://example.com/h510.jpg',
            'description' => 'Boîtier moyen tour ATX moderne.',
            'release_year' => 2023,
            'ean' => '1234567890130'
        ]);

        CaseModel::create([
            'component_id' => $component->id,
            'form_factor' => 'ATX',
            'max_gpu_length' => 381,
            'max_cooler_height' => 165,
            'psu_form_factor' => 'ATX',
            'fan_mounts' => 4
        ]);
    }
}
