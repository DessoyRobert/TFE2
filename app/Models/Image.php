<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Image
 *
 * Permet de gérer toutes les images du site de manière flexible et centralisée.
 * Utilise une relation polymorphique pour rattacher une image à n'importe quel modèle
 * (composant, build, utilisateur, etc.) sans multiplier les tables ou relations spécifiques.
 */
class Image extends Model
{
    use HasFactory;

    /**
     * Table liée à ce modèle (optionnel, car 'images' est la convention Laravel)
     */
    protected $table = 'images';

    /**
     * Champs pouvant être assignés en masse.
     * - url : lien de l'image (hébergement local ou cloud type Cloudinary)
     * - imageable_id/imageable_type : gestion de la relation polymorphique
     */
    protected $fillable = [
        'url',
        'imageable_id',
        'imageable_type',
    ];

    /**
     * Relation polymorphique Eloquent
     * 
     * Permet à une image d'être rattachée à n'importe quel modèle de l'application
     * (ex: Component, Build, User...) via l'API Laravel morphTo().
     *
     * Exemple d’utilisation :
     *   $component->images   // Récupère toutes les images du composant
     *   $image->imageable    // Récupère le modèle (component/build/etc.) auquel l'image est liée
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
