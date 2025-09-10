<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // 👈 nécessaire pour scopeWithLiveTotal

use App\Models\User;
use App\Models\Image;
use App\Models\OrderItem;
use App\Models\Component;

/**
 * Class Build
 *
 * Représente une configuration PC créée par un utilisateur.
 * Le prix d'un build est toujours calculé à l'affichage (display_total),
 * tandis que le prix d'une commande est figé au moment de sa création.
 */
class Build extends Model
{
    protected $fillable = [
        'name','user_id','img_url','description','price','is_public','build_code',
        'total_price','component_count',
        'is_featured','featured_rank',
    ];

    protected $casts = [
        'price'           => 'float',
        'total_price'     => 'float',
        'component_count' => 'integer',
        'is_public'       => 'boolean',
        'is_featured'     => 'boolean',
        'featured_rank'   => 'integer',
    ];

    /**
     * Accessors ajoutés automatiquement lors de la sérialisation.
     */
    protected $appends = [
        'calculated_total',
        'display_total',
    ];

    /**
     * Composants liés via la table pivot build_component.
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'build_component')
            ->withPivot(['quantity', 'price_at_addition'])
            ->withTimestamps();
    }

    /**
     * Propriétaire du build.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Images polymorphes liées au build.
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Lignes de commande polymorphes où ce build apparaît comme "purchasable".
     */
    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'purchasable');
    }

    /**
     * Somme dynamique des composants en tenant compte des quantités et
     * en privilégiant price_at_addition s'il existe dans le pivot.
     */
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

    /**
     * Valeur d'affichage priorisant la somme dynamique des composants.
     * Fallback ultime: price (legacy) s'il n'y a pas de composants.
     */
    public function getDisplayTotalAttribute(): float
    {
        $calc = (float) ($this->calculated_total ?? 0);
        if ($calc > 0) return round($calc, 2);

        return round((float) ($this->price ?? 0), 2);
    }

    /**
     * Somme utilitaire d'une collection de composants avec pivot.
     */
    private function sumComponentsCollection($components): float
    {
        $sum = 0.0;
        foreach ($components as $c) {
            $qty  = (int) ($c->pivot->quantity ?? 1);
            $unit = $c->pivot->price_at_addition ?? $c->price ?? 0;
            $sum += ((float) $unit) * $qty;
        }
        return round($sum, 2);
    }

    /**
     * Met à jour les champs de cache total_price / component_count si présents en DB.
     */
    public function recalculateTotals(): void
    {
        $this->loadMissing(['components' => function ($q) {
            $q->select('components.id', 'components.price')
              ->withPivot(['quantity', 'price_at_addition']);
        }]);

        $total = 0.0;
        $count = 0;

        foreach ($this->components as $component) {
            $qty  = (int) ($component->pivot->quantity ?? 1);
            $unit = (float) ($component->pivot->price_at_addition ?? $component->price ?? 0);
            $total += $unit * $qty;
            $count += $qty;
        }

        $updates = [];
        if (Schema::hasColumn('builds', 'total_price')) {
            $updates['total_price'] = round($total, 2);
        }
        if (Schema::hasColumn('builds', 'component_count')) {
            $updates['component_count'] = $count;
        }

        if ($updates) {
            $this->forceFill($updates)->save();
        }
    }

    /**
     * Scope pratique pour charger les données nécessaires au pricing.
     */
    public function scopeForPricing($query)
    {
        return $query->with(['components' => function ($q) {
            $q->select('components.id', 'components.price')
              ->withPivot(['quantity', 'price_at_addition']);
        }]);
    }

    /**
     * Ajoute une colonne "live_total" (SQL) basée sur le prix ACTUEL des composants.
     */
    public function scopeWithLiveTotal($q)
    {
        $liveTotalSub = DB::table('build_component')
            ->join('components', 'components.id', '=', 'build_component.component_id')
            ->selectRaw('COALESCE(SUM(COALESCE(components.price,0) * COALESCE(build_component.quantity,1)), 0)')
            ->whereColumn('build_component.build_id', 'builds.id');

        return $q->select('builds.*')->selectSub($liveTotalSub, 'live_total');
    }
}
