<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

/**
 * Modèle Build
 *
 * Représente une configuration PC créée par un utilisateur.
 * Les composants liés sont stockés via la table pivot build_component.
 */
class Build extends Model
{
    /**
     * Colonnes modifiables en masse.
     */
    protected $fillable = [
        'name',             // Nom du build/configuration
        'user_id',          // Référence vers le créateur (User)
        'img_url',          // Lien vers l'image de la config (optionnel)
        'description',      // Description du build
        'price',            // (legacy) Prix total éventuellement saisi à la main
        'build_code',       // Code unique partage
        'total_price',      // Prix total persisté (recalculé après attach/sync)
        'component_count',  // Nombre total d'items (somme des quantités)
    ];

    /**
     * Casts pour cohérence typée.
     */
    protected $casts = [
        'price'           => 'float',
        'total_price'     => 'float',
        'component_count' => 'integer',
    ];

    /**
     * Champs calculés exposés au JSON/Inertia.
     */
    protected $appends = [
        'calculated_total',
        'display_total',
    ];

    /* =======================================================================
     |  Relations
     * ======================================================================= */

    /**
     * Relation N-N vers Component via la table pivot build_component.
     * Champs pivot supportés :
     *  - quantity (champ actuel)
     *  - price_at_addition (snapshot du prix à l'ajout, si présent)
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'build_component')
            ->withPivot(['quantity', 'price_at_addition'])
            ->withTimestamps();
    }

    /**
     * Créateur du build.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Images (polymorphe).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Lignes de commande liées (polymorphe).
     */
    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'purchasable');
    }

    /* =======================================================================
     |  Accessors
     * ======================================================================= */

    /**
     * Total calculé dynamiquement à partir des composants.
     *
     * Priorité prix:
     *  - pivot.price_at_addition si présent (snapshot)
     *  - sinon component.price
     *  - sinon component.unit_price
     * Quantité:
     *  - pivot.quantity (défaut = 1)
     *
     * Ne dépend pas d'un eager-load préalable : recharge les composants si besoin.
     */
    public function getCalculatedTotalAttribute(): float
    {
        // Si déjà chargé → utiliser la collection en mémoire
        if ($this->relationLoaded('components')) {
            return $this->sumComponentsCollection($this->components);
        }

        // Sinon, récupération minimale depuis la DB
        $rows = $this->components()
            ->select('components.id', 'components.price', 'components.unit_price')
            ->withPivot(['quantity', 'price_at_addition'])
            ->get();

        return $this->sumComponentsCollection($rows);
    }

    /**
     * Total destiné à l'affichage.
     * Priorité :
     *  - total calculé
     *  - total persisté (total_price)
     *  - champ legacy (price)
     */
    public function getDisplayTotalAttribute(): float
    {
        $calc = (float)($this->calculated_total ?? 0);
        if ($calc > 0) {
            return $calc;
        }

        $stored = (float)($this->total_price ?? 0);
        if ($stored > 0) {
            return round($stored, 2);
        }

        return round((float)($this->price ?? 0), 2);
    }

    /**
     * Additionne proprement une collection de composants liés.
     */
    private function sumComponentsCollection($components): float
    {
        $sum = 0.0;

        foreach ($components as $c) {
            $qty = (int)($c->pivot->quantity ?? 1);

            // On choisit le prix par priorité : snapshot > price > unit_price > 0
            $unit = $c->pivot->price_at_addition
                ?? $c->price
                ?? $c->unit_price
                ?? 0;

            $sum += ((float)$unit) * $qty;
        }

        return round($sum, 2);
    }

    /* =======================================================================
     |  Helpers / Maintenance
     * ======================================================================= */

    /**
     * Recalcule et persiste les totaux dénormalisés sur le build.
     */
    public function recalculateTotals(): void
    {
        $this->loadMissing(['components' => function ($q) {
            $q->select('components.id', 'components.price', 'components.unit_price')
              ->withPivot(['quantity', 'price_at_addition']);
        }]);

        $total = 0.0;
        $count = 0;

        foreach ($this->components as $component) {
            $qty  = (int)($component->pivot->quantity ?? 1);
            $unit = (float)($component->pivot->price_at_addition ?? $component->price ?? $component->unit_price ?? 0);
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

        if (!empty($updates)) {
            $this->forceFill($updates)->save();
        }
    }

    /* =======================================================================
     |  Scopes utilitaires
     * ======================================================================= */

    /**
     * Scope: charge le strict nécessaire pour afficher un prix fiable.
     *
     * Exemple:
     * Build::forPricing()->where('user_id', auth()->id())->get();
     */
    public function scopeForPricing($query)
    {
        return $query->with(['components' => function ($q) {
            $q->select('components.id', 'components.price', 'components.unit_price')
              ->withPivot(['quantity', 'price_at_addition']);
        }]);
    }
}
