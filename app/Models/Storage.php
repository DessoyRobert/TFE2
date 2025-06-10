<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Storage
 *
 * Représente un périphérique de stockage (SSD, HDD, NVMe, etc.).
 * Toutes les informations génériques (nom, marque, prix, etc.) sont stockées dans Component.
 * Ce modèle conserve uniquement les propriétés propres à chaque type de stockage.
 */
class Storage extends Model
{
    protected $fillable = [
        'component_id',  // Référence vers Component (clé étrangère)
        'type',          // Type de stockage (ex: SSD, HDD, NVMe)
        'capacity_gb',   // Capacité (en Go)
        'interface',     // Interface de connexion (ex: SATA3, PCIe Gen4, etc.)
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
