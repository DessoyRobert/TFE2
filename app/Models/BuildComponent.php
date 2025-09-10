<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Modèle BuildComponent (Table Pivot)
 *
 * Représente la relation de type "many-to-many" entre les builds (configurations de PC) et les composants.
 * Chaque entrée associe un build à un composant spécifique.
 * Ce modèle facilite l'ajout de champs supplémentaires à la relation (quantité, rôle du composant, etc.).
 */
class BuildComponent extends Pivot
{
    // Par défaut, Laravel attend le nom au pluriel (build_componentS)
    protected $table = 'build_components';

    // Champs assignables en masse (id des builds et des composants)
    protected $fillable = [
        'build_id',        // Référence vers la table builds
        'component_id',    // Référence vers la table components
        // Ajouter d'autres champs ici si besoin (ex: 'quantity', 'slot', 'notes', etc.)
    ];
}
