<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompatibilityRule extends Model
{
    protected $fillable = [
        'component_type_a_id',
        'component_type_b_id',
        'rule_type',
        'field_a',
        'field_b',
        'operator',
        'description',
    ];

    public function typeA()
    {
        return $this->belongsTo(ComponentType::class, 'component_type_a_id');
    }

    public function typeB()
    {
        return $this->belongsTo(ComponentType::class, 'component_type_b_id');
    }
}
