<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponentType extends Model
{
    use HasFactory;

    // Table liée (optionnel si le nom correspond à Laravel convention)
    protected $table = 'component_types';

    // Les champs assignables en masse
    protected $fillable = [
        'name',
    ];

    // Relations : Un type a plusieurs components
    public function components()
    {
        return $this->hasMany(Component::class);
    }
}
