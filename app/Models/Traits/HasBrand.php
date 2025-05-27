<?php

namespace App\Models\Traits;

use App\Models\Brand;

trait HasBrand
{
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
