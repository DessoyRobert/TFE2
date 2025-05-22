<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    // Nom de la table si jamais tu veux le préciser explicitement (optionnel)
    // protected $table = 'brands';

    protected $fillable = ['name'];

    public function components()
    {
        return $this->hasMany(Component::class);
    }
}
