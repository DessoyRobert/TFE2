<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PsuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $typeId = DB::table('component_types')->where('name', 'psu')->value('id');
        $categoryId = DB::table('categories')->where('name', 'like', '%psu%')->value('id');

        $psus = [
            ['Corsair RM750x', 750, '80+ Gold', true, 'Corsair'],
            ['EVGA SuperNOVA 650 G5', 650, '80+ Gold', true, 'EVGA'],
            ['Seasonic Focus GX-850', 850, '80+ Gold', true, 'Seasonic'],
            ['Cooler Master MWE 650', 650, '80+ Bronze', false, 'Cooler Master'],
            ['Be Quiet! Pure Power 11 600W', 600, '80+ Gold', false, 'be quiet!'],
            ['Thermaltake Toughpower GF1 750W', 750, '80+ Gold', true, 'Thermaltake'],
            ['EVGA 500 W1', 500, '80+', false, 'EVGA'],
            ['Seasonic S12III 650', 650, '80+ Bronze', false, 'Seasonic'],
            ['MSI MPG A850G', 850, '80+ Gold', true, 'MSI'],
        ];

        foreach ($psus as [$name, $wattage, $certification, $modular, $brandName]) {
            $brandId = DB::table('brands')->where('name', 'like', "%$brandName%")->value('id') ?? 1;

            $componentId = DB::table('components')->insertGetId([
                'name' => $name,
                'component_type_id' => $typeId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('psus')->insert([
                'component_id' => $componentId,
                'wattage' => $wattage,
                'efficiency_certification' => $certification,
                'is_modular' => $modular,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
