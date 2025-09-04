<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Component (composant générique)
 *
 * Représente l'entité de base de chaque composant du PC (CPU, GPU, RAM, etc.)
 * Utilise l'héritage Eloquent : chaque type de composant (Cpu, Gpu, ...) a sa table dédiée.
 */
class Component extends Model
{
    protected $fillable = [
        'name',                // Nom du composant
        'brand_id',            // Clé étrangère vers Brand
        'component_type_id',   // Clé étrangère vers ComponentType
        'category_id',         // Clé étrangère vers Category (optionnelle)
        'price',               // Prix public (DECIMAL en DB, cast en float ici)
        'img_url',             // Lien image (Cloudinary ou local)
        'description',         // Description marketing / technique
        'release_year',        // Année de sortie (optionnel)
        'ean',                 // Code-barres / référence fabricant
    ];

    protected $casts = [
        'price'        => 'float',
        'release_year' => 'integer',
    ];

    /**
     * Relations de base
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function type()
    {
        return $this->belongsTo(ComponentType::class, 'component_type_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relations spécialisées : chaque "fille" possède un composant parent (1–1)
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
     */
    public function builds()
    {
        return $this->belongsToMany(Build::class, 'build_component')
            ->withTimestamps();
    }

    /**
     * Images (polymorphe)
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Lignes de commande liées (polymorphe)
     */
    public function orderItems()
    {
        return $this->morphMany(\App\Models\OrderItem::class, 'purchasable');
    }

    /**
     * Accessor destiné à l'affichage (ex: utiliser ce champ dans Vue).
     */
    public function getDisplayPriceAttribute(): float
    {
        return round((float) ($this->price ?? 0), 2);
    }

    /**
     * Prix "snapshot" à enregistrer dans la pivot lors de l'ajout au build.
     * Permet d'évoluer plus tard (promo, prix B2B, etc.) sans toucher aux contrôleurs.
     */
    public function snapshotPrice(): float
    {
        return round((float) ($this->price ?? 0), 2);
    }
}
