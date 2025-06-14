<?php

namespace App\Http\Controllers;

use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    public function index(Request $request)
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

        $components = $query->paginate($perPage);

        $components->getCollection()->transform(function ($component) {
            return [
                'id'    => $component->id,
                'name'  => $component->name,
                'brand' => $component->brand->name ?? '',
                'type'  => $component->type->name ?? '',
                'price' => $component->price,
            ];
        });

        return response()->json($components);
    }
    public function show($id)
    {
        $component = Component::with([
            'brand', 'type', 'cpu', 'gpu', 'ram',
            'motherboard', 'storage', 'psu', 'cooler', 'casemodel'
        ])->findOrFail($id);
        return response()->json($component);
    }

    // GET /components/{component} (fiche détaillée Inertia)
    public function showPage(Component $component)
    {
        $component->load([
            'brand', 'type', 'cpu', 'gpu', 'ram',
            'motherboard', 'storage', 'psu', 'cooler', 'casemodel'
        ]);
        return \Inertia\Inertia::render('Components/ShowAdd', [
            'component' => $component,
            'type' => strtolower(optional($component->type)->name ?? ''),
        ]);
    }
    
}
