<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Build
 *
 * Représente une configuration PC créée par un utilisateur.
 * Toutes les informations spécifiques à la config sont stockées ici.
 * La relation "many-to-many" avec Component permet d’associer un nombre variable de composants à chaque build.
 */
class Build extends Model
{
    protected $fillable = [
        'name',         // Nom du build/configuration
        'user_id',      // Référence vers le créateur (User)
        'img_url',      // Lien vers l'image de la config (optionnel)
        'description',  // Description du build
        'price',        // Prix total de la configuration (optionnel)
    ];

    /**
     * Relation N-N vers Component via la table pivot build_components.
     * Permet d’associer de multiples composants à un build.
     */
    public function components()
    {
        return $this->belongsToMany(Component::class, 'build_component');
    }

    /**
     * Relation vers l'utilisateur qui a créé le build.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
