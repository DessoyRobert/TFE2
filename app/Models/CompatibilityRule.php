<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle CompatibilityRule
 *
 * Cette entité permet de définir dynamiquement des règles de compatibilité entre différents types de composants
 * (ex : compatibilité socket CPU ↔ carte mère, RAM supportée, etc).
 * 
 * Le modèle permet d’ajouter, modifier ou supprimer des règles sans toucher au code métier,
 * garantissant une grande flexibilité et évolutivité du configurateur.
 */
class CompatibilityRule extends Model
{
    protected $fillable = [
        'component_type_a_id',   // Premier type de composant concerné (ex : CPU)
        'component_type_b_id',   // Deuxième type de composant concerné (ex : carte mère)
        'rule_type',             // Type de règle (ex: "equal", "contains", "min", "max", etc.)
        'field_a',               // Champ du type A à comparer (ex : socket)
        'field_b',               // Champ du type B à comparer (ex : socket)
        'operator',              // Opérateur de comparaison (ex: '=', '>=', '<=', 'in', etc.)
        'description',           // Description humaine de la règle
    ];

    /**
     * Relation vers le type de composant A.
     */
    public function componentTypeA()
    {
        return $this->belongsTo(ComponentType::class, 'component_type_a_id');
    }

    /**
     * Relation vers le type de composant B.
     */
    public function componentTypeB()
    {
        return $this->belongsTo(ComponentType::class, 'component_type_b_id');
    }
}
