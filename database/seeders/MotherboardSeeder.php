<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MotherboardSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $typeId = DB::table('component_types')->where('name', 'motherboard')->value('id');
        $categoryId = DB::table('categories')->where('name', 'like', '%motherboard%')->value('id');

        $motherboards = [
            ['MSI B550 Tomahawk', 'AM4', 'B550', 'ATX', 4, 128, 2, 2, 6, 'MSI'],
            ['ASUS ROG Strix B550-F', 'AM4', 'B550', 'ATX', 4, 128, 2, 2, 6, 'ASUS'],
            ['Gigabyte X570 Aorus Elite', 'AM4', 'X570', 'ATX', 4, 128, 3, 2, 6, 'Gigabyte'],
            ['ASRock B450M Pro4', 'AM4', 'B450', 'Micro-ATX', 4, 64, 1, 1, 4, 'ASRock'],
            ['ASUS TUF Gaming Z690-Plus', 'LGA1700', 'Z690', 'ATX', 4, 128, 3, 2, 6, 'ASUS'],
            ['MSI MAG B660M Mortar', 'LGA1700', 'B660', 'Micro-ATX', 4, 128, 2, 2, 4, 'MSI'],
            ['Gigabyte B760 Aorus Elite AX', 'LGA1700', 'B760', 'ATX', 4, 128, 2, 3, 4, 'Gigabyte'],
            ['ASRock X670E Steel Legend', 'AM5', 'X670E', 'ATX', 4, 128, 3, 4, 6, 'ASRock'],
            ['ASUS Prime A520M-A', 'AM4', 'A520', 'Micro-ATX', 2, 64, 1, 1, 4, 'ASUS'],
            ['MSI PRO B650M-A', 'AM5', 'B650', 'Micro-ATX', 4, 128, 2, 2, 4, 'MSI'],
        ];

        foreach ($motherboards as [$name, $socket, $chipset, $formFactor, $ramSlots, $maxRam, $pcieSlots, $m2Slots, $sataPorts, $brandName]) {
            $brandId = DB::table('brands')->where('name', 'like', "%$brandName%")->value('id') ?? 1;

            $componentId = DB::table('components')->insertGetId([
                'name' => $name,
                'component_type_id' => $typeId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('motherboards')->insert([
                'component_id' => $componentId,
                'socket' => $socket,
                'chipset' => $chipset,
                'form_factor' => $formFactor,
                'ram_slots' => $ramSlots,
                'max_ram' => $maxRam,
                'pcie_slots' => $pcieSlots,
                'm2_slots' => $m2Slots,
                'sata_ports' => $sataPorts,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
