<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasComponents;

class Brand extends Model
{
    use HasComponents;

    protected $fillable = [
        'name',
    ];
}
