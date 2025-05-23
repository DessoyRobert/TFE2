<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Build extends Model
{
    protected $fillable = [
        'name',
        'user_id', // Retire si tu ne veux pas lier à un utilisateur
    ];

    public function components()
    {
        return $this->belongsToMany(Component::class, 'build_components')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
