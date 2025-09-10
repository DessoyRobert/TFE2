<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Category
 *
 * Permet de regrouper les composants par grande famille (ex : Gaming, Bureautique, Workstation, etc.).
 * Cette abstraction facilite la gestion des filtres et la navigation côté utilisateur.
 */
class Category extends Model
{
    protected $fillable = [
        'name', // Nom de la catégorie (ex: "Gaming", "Professionnel", etc.)
    ];

    /**
     * Relation 1-N vers Component.
     * Permet de récupérer tous les composants appartenant à cette catégorie.
     */
    public function components()
    {
        return $this->hasMany(Component::class);
    }
}
