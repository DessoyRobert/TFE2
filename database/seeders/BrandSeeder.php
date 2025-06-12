<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $brands = [
            'AMD',
            'Intel',
            'NVIDIA',
            'ASUS',
            'MSI',
            'Gigabyte',
            'ASRock',
            'EVGA',
            'Sapphire',
            'PowerColor',
            'Zotac',
            'Palit',
            'Gainward',
            'XFX',
            'PNY',
            'Corsair',
            'G.Skill',
            'Kingston',
            'Patriot',
            'Crucial',
            'Samsung',
            'Seagate',
            'Western Digital',
            'Toshiba',
            'ADATA',
            'TeamGroup',
            'Ballistix',
            'HyperX',
            'Noctua',
            'be quiet!',
            'Cooler Master',
            'NZXT',
            'Deepcool',
            'Arctic',
            'Phanteks',
            'Fractal Design',
            'Lian Li',
            'Thermaltake',
            'In Win',
            'SilverStone',
            'Chieftec',
            'Sharkoon',
            'Seasonic',
            'FSP',
            'Antec',
            'Enermax',
            'Fortron',
            'Super Flower',
            'BitFenix',
            'Xilence',
            'Montech',
            'AeroCool',
            'Rosewill',
            'Cougar',
            'LEPA',
            'XPG',
            'Viper',
            'OCZ',
            'Colorful',
            'Inno3D',
            'Matrox',
            'Club 3D',
            'Sparkle',
            'Biostar',
            'Foxconn',
            'Elitegroup (ECS)',
            'Apple',
            'Google',
            'Dell',
            'HP',
            'Lenovo',
            'Microsoft',
            'Acer',
            'Razer',
            'Alienware',
            'Origin',
            'Gigabyte Aorus',
            'ASUS ROG',
            'MSI Gaming',
        ];

        foreach ($brands as $name) {
            DB::table('brands')->insert([
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
