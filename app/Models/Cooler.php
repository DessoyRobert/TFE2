<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Cooler
 *
 * Représente un système de refroidissement pour PC (aircooling, watercooling, etc).
 * Centralise les informations spécifiques (type, nombre de ventilateurs, etc.)
 * Les infos générales (nom, marque, prix, etc.) sont stockées dans le modèle Component.
 */
class Cooler extends Model
{
    protected $fillable = [
        'component_id',         // Référence vers le Component parent
        'type',                 // Type de refroidissement (ex: Air, Water, etc.)
        'fan_count',            // Nombre de ventilateurs supportés ou inclus
        'compatible_sockets',   // Liste des sockets compatibles (JSON ou chaîne)
        'max_tdp',              // Dissipation thermique maximale supportée (en Watts)
        'height_mm',            // Hauteur du ventirad/radiateur en mm
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
