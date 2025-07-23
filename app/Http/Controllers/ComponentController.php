<?php

namespace App\Http\Controllers;

use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    /**
     * Affiche une liste paginée des composants via Inertia (interface utilisateur).
     */
    public function indexPage(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $query = Component::with(['brand', 'type']);

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

        $components->getCollection()->transform(function ($component) {
            return [
                'id'    => $component->id,
                'name'  => $component->name,
                'brand' => $component->brand->name ?? '',
                'type'  => $component->type->name ?? '',
                'price' => $component->price,
            ];
        });

        return inertia('Components/Index', [
            'components' => $components,
            'filters' => $request->only(['search', 'sortBy', 'sortDesc']),
        ]);
    }

    /**
     * Retourne les données JSON complètes d'un composant (API ou debug).
     */
    public function show($id)
    {
        $component = Component::with([
            'brand', 'type', 'cpu', 'gpu', 'ram',
            'motherboard', 'storage', 'psu', 'cooler', 'casemodel'
        ])->findOrFail($id);

        return response()->json($component);
    }

    /**
     * Page de détails d’un composant avec vue enrichie + bouton "Ajouter au build".
     * Utilisée pour afficher dynamiquement tout type de composant.
     */
    public function showDetailPage(Component $component)
    {
        $component->load([
            'brand', 'type', 'cpu', 'gpu', 'ram',
            'motherboard', 'storage', 'psu', 'cooler', 'casemodel','images'
        ]);

        $details = collect([
            'cpu' => $component->cpu,
            'gpu' => $component->gpu,
            'ram' => $component->ram,
            'motherboard' => $component->motherboard,
            'storage' => $component->storage,
            'psu' => $component->psu,
            'cooler' => $component->cooler,
            'casemodel' => $component->casemodel,
        ])->filter()->first();

        return inertia('Components/Details', [
            'component' => [
                'id' => $component->id,
                'name' => $component->name,
                'price' => $component->price,
                'description' => $component->description,
                'img_url' => $component->img_url,
                'brand' => $component->brand,
                'type' => $component->type,
                'images' => $component->images,
            ],
            'details' => $details
                ? collect($details)->except(['id', 'component_id', 'created_at', 'updated_at'])->toArray()
                : [],
            'type' => strtolower(optional($component->type)->name ?? ''),
        ]);

    }

    /**
     * Page de détails simple (fallback ou usage secondaire, non utilisée activement).
     */
    public function detailsPage(Component $component)
    {
        $component->load([
            'brand', 'type', 'cpu', 'gpu', 'ram',
            'motherboard', 'storage', 'psu', 'cooler', 'casemodel'
        ]);

        return inertia('Components/Details', [
            'component' => $component,
            'type' => strtolower(optional($component->type)->name ?? ''),
        ]);
    }
}
