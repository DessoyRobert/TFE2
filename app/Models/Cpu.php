<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Cpu
 *
 * Représente un processeur (CPU) spécifique dans la base de données.
 * Toutes les informations génériques (nom, marque, prix, etc.) sont stockées dans Component,
 * tandis que Cpu ne gère que les attributs propres au processeur.
 */
class Cpu extends Model
{
    protected $fillable = [
        'component_id',        // Clé étrangère vers Component
        'socket',              // Socket compatible (ex: AM4, LGA1700)
        'core_count',          // Nombre de coeurs physiques
        'thread_count',        // Nombre de threads
        'base_clock',          // Fréquence de base (en GHz)
        'boost_clock',         // Fréquence maximale en boost (en GHz, optionnel)
        'tdp',                 // Enveloppe thermique (Watts)
        'integrated_graphics', // Présence ou non de GPU intégré (booléen ou string, optionnel)
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
