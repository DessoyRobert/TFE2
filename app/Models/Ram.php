<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Ram
 *
 * Représente une barrette ou un kit de mémoire vive (RAM).
 * Toutes les informations génériques (nom, marque, prix, etc.) sont stockées dans Component.
 * Ce modèle stocke uniquement les propriétés spécifiques à la RAM.
 */
class Ram extends Model
{
    protected $fillable = [
        'component_id',   // Référence vers Component (clé étrangère)
        'type',           // Type de RAM (ex: DDR4, DDR5)
        'capacity_gb',    // Capacité totale (en Go)
        'modules',        // Nombre de barrettes/modules dans le kit
        'speed_mhz',      // Fréquence (en MHz)
        'cas_latency',    // CAS Latency (optionnel)
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
