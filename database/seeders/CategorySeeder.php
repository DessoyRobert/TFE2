<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Processeur',
            'Carte graphique',
            'Mémoire',
            'Carte mère',
            'Stockage',
            'Alimentation',
            'Refroidisseur',
            'Boîtier'
        ];
        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
