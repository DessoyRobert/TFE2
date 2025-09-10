<?php

namespace App\Http\Controllers;

use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    /**
     * Liste paginée des composants (Inertia).
     * -> On charge aussi les images pour éviter les N+1 et fournir un img_url fiable.
     */
    public function indexPage(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $query = Component::with(['brand', 'type', 'images']);

        if ($search = $request->input('search')) {
            $query->where('name', 'ilike', "%$search%")
                ->orWhereHas('brand', fn($q) => $q->where('name', 'ilike', "%$search%"))
                ->orWhereHas('type', fn($q) => $q->where('name', 'ilike', "%$search%"));
        }

        if ($sortBy = $request->input('sortBy')) {
            $sortDesc = $request->boolean('sortDesc', false);
            $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $components = $query->paginate($perPage)->withQueryString();

        // Normalisation: on expose un img_url fiable (Cloudinary -> fallback)
        $components->getCollection()->transform(function ($component) {
            return [
                'id'      => $component->id,
                'name'    => $component->name,
                'brand'   => $component->brand->name ?? '',
                'type'    => $component->type->name ?? '',
                'price'   => $component->price,
                'img_url' => optional($component->images->first())->url
                            ?? $component->img_url
                            ?? '/images/default.png',
            ];
        });

        return inertia('Components/Index', [
            'components' => $components,
            'filters'    => $request->only(['search', 'sortBy', 'sortDesc']),
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

        // Priorité à Cloudinary ; sinon img_url existant ; sinon placeholder
        $component->img_url = optional($component->images->first())->url
                              ?? $component->img_url
                              ?? '/images/default.png';

        return response()->json($component);
    }

    /**
     * Page Détails (Inertia) + bouton "Ajouter au build".
     * -> Envoie un objet "component" déjà prêt pour le front (img_url fiable + images).
     */
    public function showDetailPage(Component $component)
    {
        $component->load([
            'brand', 'type', 'cpu', 'gpu', 'ram',
            'motherboard', 'storage', 'psu', 'cooler', 'casemodel', 'images'
        ]);

        // Détails spécifiques selon le type
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

        return inertia('Components/Details', [
            'component' => [
                'id'          => $component->id,
                'name'        => $component->name,
                'price'       => $component->price,
                'description' => $component->description,
                'img_url'     => optional($component->images->first())->url
                                 ?? $component->img_url
                                 ?? '/images/default.png',
                'brand'       => $component->brand,
                'type'        => $component->type,
                'images'      => $component->images, // garde la galerie complète
            ],
            'details' => $details
                ? collect($details)->except(['id', 'component_id', 'created_at', 'updated_at'])->toArray()
                : [],
            'type' => strtolower(optional($component->type)->name ?? ''),
        ]);
    }

    /**
     * Fallback simple (peu utilisée).
     */
    public function detailsPage(Component $component)
    {
        $component->load([
            'brand', 'type', 'cpu', 'gpu', 'ram',
            'motherboard', 'storage', 'psu', 'cooler', 'casemodel', 'images'
        ]);

        return inertia('Components/Details', [
            'component' => [
                'id'      => $component->id,
                'name'    => $component->name,
                'brand'   => $component->brand,
                'type'    => $component->type,
                'price'   => $component->price,
                'img_url' => optional($component->images->first())->url
                             ?? $component->img_url
                             ?? '/images/default.png',
            ],
            'type' => strtolower(optional($component->type)->name ?? ''),
        ]);
    }
}
