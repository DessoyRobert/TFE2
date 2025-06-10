<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Motherboard
 *
 * Représente une carte mère spécifique dans le système.
 * Toutes les informations communes (nom, marque, prix, image, etc.) sont stockées dans le modèle Component.
 * Les attributs définis ici sont propres à chaque carte mère.
 */
class Motherboard extends Model
{
    protected $fillable = [
        'component_id', // Référence vers le Component parent
        'socket',       // Socket CPU supporté (ex : AM4, LGA1700…)
        'chipset',      // Chipset de la carte (ex : B550, Z690…)
        'form_factor',  // Format (ATX, mATX, ITX…)
        'ram_slots',    // Nombre de slots RAM disponibles
        'max_ram',      // Quantité maximale de RAM supportée (en Go)
        'pcie_slots',   // Nombre de slots PCI Express
        'm2_slots',     // Nombre de slots M.2 NVMe
        'sata_ports',   // Nombre de ports SATA disponibles
    ];

    /**
     * Relation N-1 vers le composant principal.
     * Permet d'accéder aux infos génériques (marque, prix, image, etc.).
     */
    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
