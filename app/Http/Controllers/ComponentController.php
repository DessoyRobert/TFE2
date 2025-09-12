<?php

namespace App\Http\Controllers;

use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    protected string $fallbackImage = 'https://res.cloudinary.com/djllwl8c0/image/upload/v1753292540/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png';
    /**
     * Ajoute f_auto,q_auto[,w_*] aux URLs Cloudinary, sinon renvoie l'URL intacte.
     */
    protected function transformCdn(?string $url, ?int $w = 160): ?string
    {
        if (!$url) return $url;
        if (str_contains($url, '/upload/')) {
            $insert = 'f_auto,q_auto' . ($w ? ',w_' . (int)$w : '');
            return preg_replace('#/upload/#', '/upload/' . $insert . '/', $url, 1);
        }
        return $url;
    }

    /**
     * Liste paginée des composants (Inertia).
     * Charge aussi les images pour éviter N+1 et fournir un img_url fiable.
     */
    public function indexPage(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);

        $query = Component::with(['brand:id,name', 'type:id,name', 'images:id,imageable_id,imageable_type,url']);

        if ($search = $request->input('search')) {
            // ILIKE = Postgres ; si MySQL, remplace par LOWER(...) LIKE
            $query->where('name', 'ilike', "%{$search}%")
                ->orWhereHas('brand', fn($q) => $q->where('name', 'ilike', "%{$search}%"))
                ->orWhereHas('type', fn($q) => $q->where('name', 'ilike', "%{$search}%"));
        }

        if ($sortBy = $request->input('sortBy')) {
            $sortDesc = $request->boolean('sortDesc', false);
            $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $components = $query->paginate($perPage)->withQueryString();

        // Normalisation : expose un img_url fiable et optimisé (Cloudinary)
        $components->getCollection()->transform(function ($c) {
            $primary = optional($c->images->first())->url
                ?? $c->img_url
                ?? $this->fallbackImage;

            return [
                'id'      => $c->id,
                'name'    => $c->name,
                'brand'   => $c->brand?->name ?? '',
                'type'    => $c->type?->name ?? '',
                'price'   => (float) ($c->price ?? 0),
                'img_url' => $this->transformCdn($primary, 160),
            ];
        });

        return inertia('Components/Index', [
            'components' => $components,
            'filters'    => $request->only(['search', 'sortBy', 'sortDesc', 'per_page']),
        ]);
    }

    /**
     * JSON complet d'un composant (API / debug).
     * -> On inclut les images et on remplace img_url si Cloudinary existe.
     */
    public function show($id)
    {
        $component = Component::with([
            'brand', 'type', 'cpu', 'gpu', 'ram',
            'motherboard', 'storage', 'psu', 'cooler', 'casemodel', 'images'
        ])->findOrFail($id);

        $primary = optional($component->images->first())->url
            ?? $component->img_url
            ?? $this->fallbackImage;

        // petite opti
        $component->img_url = $this->transformCdn($primary, 640);

        return response()->json($component);
    }

    /**
     * Page Détails (Inertia) + bouton "Ajouter au build".
     */
    public function showDetailPage(Component $component)
    {
        $component->load([
            'brand', 'type', 'cpu', 'gpu', 'ram',
            'motherboard', 'storage', 'psu', 'cooler', 'casemodel', 'images'
        ]);

        $details = collect([
            'cpu'         => $component->cpu,
            'gpu'         => $component->gpu,
            'ram'         => $component->ram,
            'motherboard' => $component->motherboard,
            'storage'     => $component->storage,
            'psu'         => $component->psu,
            'cooler'      => $component->cooler,
            'casemodel'   => $component->casemodel,
        ])->filter()->first();

        $primary = optional($component->images->first())->url
            ?? $component->img_url
            ?? $this->fallbackImage;

        return inertia('Components/Details', [
            'component' => [
                'id'          => $component->id,
                'name'        => $component->name,
                'price'       => $component->price,
                'description' => $component->description,
                'img_url'     => $this->transformCdn($primary, 960),
                'brand'       => $component->brand,
                'type'        => $component->type,
                'images'      => $component->images,
            ],
            'details' => $details
                ? collect($details)->except(['id', 'component_id', 'created_at', 'updated_at'])->toArray()
                : [],
            'type' => strtolower(optional($component->type)->name ?? ''),
        ]);
    }

    public function detailsPage(Component $component)
    {
        $component->load([
            'brand', 'type', 'cpu', 'gpu', 'ram',
            'motherboard', 'storage', 'psu', 'cooler', 'casemodel', 'images'
        ]);

        $primary = optional($component->images->first())->url
            ?? $component->img_url
            ?? $this->fallbackImage;

        return inertia('Components/Details', [
            'component' => [
                'id'      => $component->id,
                'name'    => $component->name,
                'brand'   => $component->brand,
                'type'    => $component->type,
                'price'   => $component->price,
                'img_url' => $this->transformCdn($primary, 960),
            ],
            'type' => strtolower(optional($component->type)->name ?? ''),
        ]);
    }
}
