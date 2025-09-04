<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'customer_first_name', 'customer_last_name', 'customer_email', 'customer_phone',
        'shipping_address_line1', 'shipping_address_line2', 'shipping_city', 'shipping_postal_code', 'shipping_country',
        'subtotal', 'shipping_cost', 'discount_total', 'tax_total', 'grand_total',
        'status', 'payment_method', 'payment_status', 'currency', 'meta'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'payment_deadline' => 'datetime',
        'payment_received_at' => 'datetime',
        'meta' => 'array', // 👈 important pour stocker idempotency_key & co
    ];

    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }

    // Helper lisible
    protected function customerName(): Attribute {
        return Attribute::get(fn() => trim($this->customer_first_name.' '.$this->customer_last_name));
    }
}
