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
            // name, wattage, certification, modular, form_factor, price, brand
            ['Corsair RM850x', 850, '80+ Gold', true, 'ATX', 149.99, 'Corsair'],
            ['be quiet! Straight Power 11', 750, '80+ Platinum', true, 'ATX', 169.99, 'be quiet!'],
            ['Seasonic Focus GX-650', 650, '80+ Gold', true, 'ATX', 104.99, 'Seasonic'],
            ['EVGA SuperNOVA 550 G3', 550, '80+ Gold', true, 'ATX', 89.99, 'EVGA'],
            ['Cooler Master MWE Gold 650', 650, '80+ Gold', false, 'ATX', 74.99, 'Cooler Master'],
            ['Corsair SF600', 600, '80+ Gold', true, 'SFX', 129.99, 'Corsair'],
            ['FSP Hydro G Pro 850W', 850, '80+ Gold', true, 'ATX', 139.99, 'FSP'],
            ['Thermaltake Smart 500W', 500, '80+ White', false, 'ATX', 39.99, 'Thermaltake'],
            ['Seasonic Prime TX-1000', 1000, '80+ Titanium', true, 'ATX', 259.99, 'Seasonic'],
            ['Antec Earthwatts Gold Pro 750W', 750, '80+ Gold', true, 'ATX', 99.99, 'Antec'],
        ];

        foreach ($psus as [$name, $wattage, $certification, $modular, $form_factor, $price, $brandName]) {
            $brandId = DB::table('brands')->where('name', 'like', "%$brandName%")->value('id') ?? 1;

            $componentId = DB::table('components')->insertGetId([
                'name' => $name,
                'component_type_id' => $typeId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'price' => $price,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('psus')->insert([
                'component_id'   => $componentId,
                'wattage'        => $wattage,
                'certification'  => $certification,
                'modular'        => $modular,
                'form_factor'    => $form_factor,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}
