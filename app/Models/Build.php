<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

// ✅ imports des classes référencées dans les relations :
use App\Models\User;
use App\Models\Image;
use App\Models\OrderItem;

class Build extends Model
{
    // (optionnel) explicite si ta table ne suit pas la convention
    // protected $table = 'builds';

    protected $fillable = [
        'name',
        'user_id',
        'img_url',
        'description',
        'price',           // legacy: ok si encore présent en DB
        'is_public',
        'build_code',
        'total_price',
        'component_count',
    ];

    protected $casts = [
        'price'           => 'float',
        'total_price'     => 'float',
        'component_count' => 'integer',
        'is_public'       => 'boolean',
    ];

    protected $appends = [
        'calculated_total',
        'display_total',
    ];

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'build_component')
            ->withPivot(['quantity', 'price_at_addition'])
            ->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'purchasable');
    }

    public function getCalculatedTotalAttribute(): float
    {
        if ($this->relationLoaded('components')) {
            return $this->sumComponentsCollection($this->components);
        }

        $rows = $this->components()
            ->select('components.id', 'components.price')
            ->withPivot(['quantity', 'price_at_addition'])
            ->get();

        return $this->sumComponentsCollection($rows);
        }

    public function getDisplayTotalAttribute(): float
    {
        $stored = (float)($this->total_price ?? 0);
        if ($stored > 0) return round($stored, 2);

        $calc = (float)($this->calculated_total ?? 0);
        if ($calc > 0) return round($calc, 2);

        return round((float)($this->price ?? 0), 2);
    }

    private function sumComponentsCollection($components): float
    {
        $sum = 0.0;
        foreach ($components as $c) {
            $qty  = (int)($c->pivot->quantity ?? 1);
            $unit = $c->pivot->price_at_addition ?? $c->price ?? 0;
            $sum += ((float)$unit) * $qty;
        }
        return round($sum, 2);
    }

    public function recalculateTotals(): void
    {
        $this->loadMissing(['components' => function ($q) {
            $q->select('components.id', 'components.price')
              ->withPivot(['quantity', 'price_at_addition']);
        }]);

        $total = 0.0;
        $count = 0;

        foreach ($this->components as $component) {
            $qty  = (int)($component->pivot->quantity ?? 1);
            $unit = (float)($component->pivot->price_at_addition ?? $component->price ?? 0);
            $total += $unit * $qty;
            $count += $qty;
        }

        $updates = [];
        if (Schema::hasColumn('builds', 'total_price'))     $updates['total_price'] = round($total, 2);
        if (Schema::hasColumn('builds', 'component_count')) $updates['component_count'] = $count;

        if ($updates) $this->forceFill($updates)->save();
    }

    public function scopeForPricing($query)
    {
        return $query->with(['components' => function ($q) {
            $q->select('components.id', 'components.price')
              ->withPivot(['quantity', 'price_at_addition']);
        }]);
    }
}
