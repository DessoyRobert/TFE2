<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    // Table liée (par défaut 'images', à modifier si tu changes de nom)
    protected $table = 'images';

    // Champs assignables en masse
    protected $fillable = [
        'url',          // Lien de l'image (Cloudinary ou autre)
        'imageable_id', // Pour la relation polymorphique (optionnel, à activer si tu veux lier à différents modèles)
        'imageable_type',
        // Ajoute d'autres champs si besoin (ex : 'alt', 'position', etc.)
    ];

    /**
     * Relation polymorphique (ex: une image peut appartenir à un composant, build, etc.)
     * 
     * Pour l'utiliser, tu dois avoir les colonnes imageable_id et imageable_type dans ta table.
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
