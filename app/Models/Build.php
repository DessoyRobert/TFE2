<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Build extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'img_url',
        'description',
        'price',
    ];

    public function components()
    {
        return $this->belongsToMany(Component::class, 'build_component');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
