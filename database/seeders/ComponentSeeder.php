<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\ComponentType;
use App\Models\Brand;
use App\Models\Category;

class ComponentSeeder extends Seeder
{
    public function run()
    {   

        // Récupération des types
        $cpuTypeId = ComponentType::where('name', 'cpu')->first()?->id;
        $gpuTypeId = ComponentType::where('name', 'gpu')->first()?->id;
        $ramTypeId = ComponentType::where('name', 'ram')->first()?->id;

        // Récupération des brands (exemple : AMD, NVIDIA, Corsair)
        $amdBrandId = Brand::where('name', 'AMD')->first()?->id;
        $nvidiaBrandId = Brand::where('name', 'NVIDIA')->first()?->id;
        $corsairBrandId = Brand::where('name', 'Corsair')->first()?->id;

        // Récupération des catégories (exemple : "Processeur", "Carte Graphique", "Mémoire")
        $cpuCategoryId = Category::where('name', 'Processeur')->first()?->id;
        $gpuCategoryId = Category::where('name', 'Carte graphique')->first()?->id;
        $ramCategoryId = Category::where('name', 'Mémoire')->first()?->id;

        

        // Sécurité : stop si un type, une brand ou une catégorie n'est pas trouvée
        if (
            !$cpuTypeId || !$gpuTypeId || !$ramTypeId ||
            !$amdBrandId || !$nvidiaBrandId || !$corsairBrandId ||
            !$cpuCategoryId || !$gpuCategoryId || !$ramCategoryId
        ) {
            throw new \Exception("Un des ComponentType, Brand ou Category est manquant !");
        }

        // CPU
        Component::create([
            'name' => 'AMD Ryzen 7 5800X',
            'component_type_id' => $cpuTypeId,
            'brand_id' => $amdBrandId,
            'category_id' => $cpuCategoryId,
            'description' => '8 cores, 16 threads, 3.8 GHz base clock.',
            // Ajoute d'autres champs si besoin !
        ]);

        // GPU
        Component::create([
            'name' => 'NVIDIA RTX 4080',
            'component_type_id' => $gpuTypeId,
            'brand_id' => $nvidiaBrandId,
            'category_id' => $gpuCategoryId,
            'description' => '16GB GDDR6X, Ray tracing, DLSS 3.',
        ]);

        // RAM
        Component::create([
            'name' => 'Corsair Vengeance 16GB DDR4',
            'component_type_id' => $ramTypeId,
            'brand_id' => $corsairBrandId,
            'category_id' => $ramCategoryId,
            'description' => '3200 MHz CL16, kit 2x8GB.',
        ]);
    }
}
