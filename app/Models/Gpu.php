<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Gpu
 *
 * Représente une carte graphique (GPU) spécifique.
 * Toutes les informations génériques (nom, marque, prix, etc.) sont stockées dans Component,
 * tandis que Gpu gère uniquement les propriétés spécifiques aux cartes graphiques.
 */
class Gpu extends Model
{
    protected $fillable = [
        'component_id',        // Clé étrangère vers Component
        'chipset',             // Nom du chipset (ex: RTX 4080, RX 7900 XT, etc.)
        'memory',              // Quantité de mémoire vidéo (ex: 16GB)
        'base_clock',          // Fréquence de base (MHz/GHz, optionnel)
        'boost_clock',         // Fréquence maximale en boost (optionnel)
        'length_mm',           // Longueur en mm (pour compatibilité boîtier)
        'tdp',                 // Enveloppe thermique (optionnel)
        'recommended_wattage', // Puissance minimale recommandée pour l'alimentation
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
