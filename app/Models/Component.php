<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Component (composant générique)
 * 
 * Représente l'entité de base de chaque composant du PC (CPU, GPU, RAM, etc.)
 * Utilise l'héritage Eloquent : chaque type de composant (Cpu, Gpu, ...) a une table spécifique
 */
class Component extends Model
{
    protected $fillable = [
        'name',                // Nom du composant
        'brand_id',            // Clé étrangère vers Brand
        'component_type_id',   // Clé étrangère vers ComponentType
        'category_id',         // Clé étrangère vers Category (si tu l'as bien dans la table)
        'price',               // Prix public
        'img_url',             // Lien vers image (Cloudinary ou local)
        'description',         // Description marketing ou technique
        'release_year',        // Année de sortie (optionnel)
        'ean',                 // Code-barre ou référence fabricant
    ];

    /**
     * Relation vers la marque du composant (Brand)
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Relation vers le type de composant (CPU, GPU, etc.)
     */
    public function type()
    {
        return $this->belongsTo(ComponentType::class, 'component_type_id');
    }

    /**
     * Relation vers la catégorie (optionnelle, ex: gaming/professionnel)
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relations spécialisées : chaque composant "fille" possède un et un seul composant parent
    public function cpu()         { return $this->hasOne(Cpu::class, 'component_id'); }
    public function gpu()         { return $this->hasOne(Gpu::class, 'component_id'); }
    public function ram()         { return $this->hasOne(Ram::class, 'component_id'); }
    public function motherboard() { return $this->hasOne(Motherboard::class, 'component_id'); }
    public function storage()     { return $this->hasOne(Storage::class, 'component_id'); }
    public function psu()         { return $this->hasOne(Psu::class, 'component_id'); }
    public function cooler()      { return $this->hasOne(Cooler::class, 'component_id'); }
    public function casemodel()   { return $this->hasOne(CaseModel::class, 'component_id'); }


    /**
     * Relation N-N avec Build (table pivot build_component)
     * Permet de récupérer tous les builds qui utilisent ce composant
     */
    public function builds()
    {
        return $this->belongsToMany(Build::class, 'build_component')
            ->withTimestamps();
    }
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
    public function orderItems() {
        return $this->morphMany(\App\Models\OrderItem::class, 'purchasable');
    }
}
