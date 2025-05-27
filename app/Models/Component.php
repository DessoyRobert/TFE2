<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $fillable = [
        'name',
        'brand_id',        // Clé étrangère vers Brand
        'component_type_id',    // Clé étrangère vers ComponentType
        'price',
        'img_url',
        'description',
        'release_year',
        'ean',
    ];

    // Relation vers le type (nouvelle structure)
    public function type()
    {
        return $this->belongsTo(ComponentType::class, 'component_type_id');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    // Relations spécialisées (ok à garder)
    public function cpu()         { return $this->hasOne(Cpu::class); }
    public function gpu()         { return $this->hasOne(Gpu::class); }
    public function ram()         { return $this->hasOne(Ram::class); }
    public function motherboard() { return $this->hasOne(Motherboard::class); }
    public function storage()     { return $this->hasOne(Storage::class); }
    public function psu()         { return $this->hasOne(Psu::class); }
    public function cooler()      { return $this->hasOne(Cooler::class); }
    public function casemodel()   { return $this->hasOne(CaseModel::class); }

    // Table pivot builds/components
    public function builds()
    {
        return $this->belongsToMany(Build::class, 'build_component')
            ->withTimestamps();
    }
    

}
