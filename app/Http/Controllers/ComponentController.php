<?php

namespace App\Http\Controllers;

use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    // GET /components (liste)
    public function index()
    {
        return Component::with(['brand', 'type'])->get()->map(function ($component) {
            return [
                'id' => $component->id,
                'name' => $component->name,
                'brand' => $component->brand->name ?? '',
                'type' => $component->type->name ?? '',
                'price' => $component->price,
                'img_url' => $component->img_url,
                'description' => $component->description,
                'release_year' => $component->release_year,
                'ean' => $component->ean,
            ];
        })->values();
    }

    // GET /components/{id} (JSON)
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
