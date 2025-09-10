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
            // name, socket, chipset, ram_type, formFactor, ramSlots, maxRam, pcieSlots, m2Slots, sataPorts, brandName
            ['MSI B550 Tomahawk', 'AM4', 'B550', 'DDR4', 'ATX', 4, 128, 2, 2, 6, 'MSI'],
            ['ASUS ROG Strix B550-F', 'AM4', 'B550', 'DDR4', 'ATX', 4, 128, 2, 2, 6, 'ASUS'],
            ['Gigabyte X570 Aorus Elite', 'AM4', 'X570', 'DDR4', 'ATX', 4, 128, 3, 2, 6, 'Gigabyte'],
            ['ASRock B450M Pro4', 'AM4', 'B450', 'DDR4', 'Micro-ATX', 4, 64, 1, 1, 4, 'ASRock'],
            ['ASUS TUF Gaming Z690-Plus', 'LGA1700', 'Z690', 'DDR5', 'ATX', 4, 128, 3, 2, 6, 'ASUS'],
            ['MSI MAG B660M Mortar', 'LGA1700', 'B660', 'DDR4', 'Micro-ATX', 4, 128, 2, 2, 4, 'MSI'],
            ['Gigabyte B760 Aorus Elite AX', 'LGA1700', 'B760', 'DDR5', 'ATX', 4, 128, 2, 3, 4, 'Gigabyte'],
            ['ASRock X670E Steel Legend', 'AM5', 'X670E', 'DDR5', 'ATX', 4, 128, 3, 4, 6, 'ASRock'],
            ['ASUS Prime A520M-A', 'AM4', 'A520', 'DDR4', 'Micro-ATX', 2, 64, 1, 1, 4, 'ASUS'],
            ['MSI PRO B650M-A', 'AM5', 'B650', 'DDR5', 'Micro-ATX', 4, 128, 2, 2, 4, 'MSI'],

            // 20 entrées auto-générées
            ['MSI X670E Series 839', 'AM5', 'X670E', 'DDR5', 'Micro-ATX', 4, 128, 2, 3, 5, 'MSI'],
            ['ASUS X670E Series 497', 'AM5', 'X670E', 'DDR5', 'Micro-ATX', 2, 128, 1, 2, 4, 'ASUS'],
            ['ASRock X670E Series 946', 'AM5', 'X670E', 'DDR5', 'ATX', 2, 64, 2, 2, 4, 'ASRock'],
            ['MSI Z690 Series 411', 'LGA1700', 'Z690', 'DDR5', 'Micro-ATX', 2, 128, 3, 2, 2, 'MSI'],
            ['ASRock Z690 Series 252', 'LGA1700', 'Z690', 'DDR4', 'Micro-ATX', 2, 128, 1, 2, 3, 'ASRock'],
            ['Gigabyte X570 Series 679', 'AM4', 'X570', 'DDR4', 'ATX', 4, 128, 2, 2, 6, 'Gigabyte'],
            ['MSI B450 Series 812', 'AM4', 'B450', 'DDR4', 'Micro-ATX', 2, 64, 1, 1, 4, 'MSI'],
            ['ASUS B660 Series 734', 'LGA1700', 'B660', 'DDR5', 'ATX', 4, 128, 3, 3, 5, 'ASUS'],
            ['Gigabyte B760 Series 201', 'LGA1700', 'B760', 'DDR4', 'ATX', 4, 128, 2, 2, 4, 'Gigabyte'],
            ['ASRock B650 Series 402', 'AM5', 'B650', 'DDR5', 'Micro-ATX', 2, 128, 2, 2, 4, 'ASRock'],
            ['MSI A320 Series 135', 'AM4', 'A320', 'DDR4', 'Micro-ATX', 2, 64, 1, 1, 4, 'MSI'],
            ['ASUS X570 Series 898', 'AM4', 'X570', 'DDR4', 'ATX', 4, 128, 2, 2, 5, 'ASUS'],
            ['ASRock A520 Series 372', 'AM4', 'A520', 'DDR4', 'Micro-ATX', 2, 64, 1, 1, 4, 'ASRock'],
            ['Gigabyte X670E Series 567', 'AM5', 'X670E', 'DDR5', 'ATX', 4, 128, 3, 3, 6, 'Gigabyte'],
            ['ASUS Z690 Series 754', 'LGA1700', 'Z690', 'DDR5', 'Micro-ATX', 2, 128, 2, 3, 4, 'ASUS'],
            ['MSI B660 Series 300', 'LGA1700', 'B660', 'DDR4', 'ATX', 4, 128, 2, 2, 5, 'MSI'],
            ['ASRock B760 Series 142', 'LGA1700', 'B760', 'DDR4', 'ATX', 4, 128, 3, 3, 4, 'ASRock'],
            ['Gigabyte B650 Series 621', 'AM5', 'B650', 'DDR5', 'Micro-ATX', 2, 128, 2, 2, 3, 'Gigabyte'],
            ['ASUS A320 Series 583', 'AM4', 'A320', 'DDR4', 'Micro-ATX', 2, 64, 1, 1, 4, 'ASUS'],
            ['MSI X670E Series 712', 'AM5', 'X670E', 'DDR5', 'ATX', 4, 128, 2, 3, 5, 'MSI'],
        ];

        foreach ($motherboards as [$name, $socket, $chipset, $ram_type, $formFactor, $ramSlots, $maxRam, $pcieSlots, $m2Slots, $sataPorts, $brandName]) {
            $brandId = DB::table('brands')->where('name', 'like', "%$brandName%")->value('id') ?? 1;

            $componentId = DB::table('components')->insertGetId([
                'name' => $name,
                'component_type_id' => $typeId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
                'price' => fake()->numberBetween(60, 350),
            ]);

            DB::table('motherboards')->insert([
                'component_id' => $componentId,
                'socket' => $socket,
                'chipset' => $chipset,
                'ram_type' => $ram_type,
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
