<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id','provider','provider_ref','amount','currency','status','payload'];
    protected $casts = ['payload' => 'array', 'amount' => 'decimal:2'];

    public function order() { return $this->belongsTo(Order::class); }
}
