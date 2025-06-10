<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle CaseModel
 *
 * Représente un boîtier PC spécifique.
 * Toutes les informations génériques (nom, marque, prix, image...) sont héritées via la relation avec Component.
 * Les attributs ci-dessous sont propres à chaque boîtier.
 */
class CaseModel extends Model
{
    protected $fillable = [
        'component_id',         // Clé étrangère vers le Component parent
        'form_factor',          // Exemple : ATX, mATX, ITX...
        'max_gpu_length',       // Longueur max GPU en mm
        'max_cooler_height',    // Hauteur max ventirad en mm
        'psu_form_factor',      // Type d'alim supporté (ATX/SFX)
        'fan_mounts',           // Nombre de slots ventilateurs (optionnel)
    ];

    /**
     * Relation N-1 vers le composant principal.
     * Permet d'accéder aux infos génériques (marque, prix, image...).
     */
    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
