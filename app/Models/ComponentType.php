<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle ComponentType
 *
 * Représente les différents types de composants matériels (ex: cpu, gpu, ram...).
 * Cette abstraction permet de lier chaque composant à sa famille, et de gérer dynamiquement
 * l’ajout de nouveaux types dans le futur.
 */
class ComponentType extends Model
{
    use HasFactory;

    /**
     * Table liée à ce modèle (optionnel ici, mais utile pour rappel)
     * Par convention, Laravel attend 'component_types'.
     */
    protected $table = 'component_types';

    /**
     * Champs autorisés à l’assignation de masse.
     * Ici, uniquement le nom du type (ex: cpu, gpu...).
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Relation 1-N : Un type de composant possède plusieurs composants.
     */
    public function components()
    {
        return $this->hasMany(Component::class);
    }
}
