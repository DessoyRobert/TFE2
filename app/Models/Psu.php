<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Psu
 *
 * Représente une alimentation PC (Power Supply Unit).
 * Toutes les informations génériques (nom, marque, prix, etc.) sont stockées dans Component.
 * Les champs ci-dessous sont spécifiques à chaque alimentation.
 */
class Psu extends Model
{
    protected $fillable = [
        'component_id',    // Référence vers Component (clé étrangère)
        'wattage',         // Puissance de l’alimentation (en Watts)
        'certification',   // Certification (ex: 80 PLUS Gold)
        'modular',         // true/false (alimentation modulaire ou non)
        'form_factor',     // Format physique (ex: ATX, SFX, etc.)
    ];

    /**
     * Relation N-1 vers le composant principal.
     * Permet d'accéder aux informations génériques (marque, prix, image...).
     */
    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
