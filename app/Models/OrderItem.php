<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'purchasable_type', 'purchasable_id',
        'quantity', 'unit_price', 'line_total', 'snapshot'
    ];

    protected $casts = [
        'snapshot' => 'array',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function purchasable(): MorphTo { return $this->morphTo(); }
}
