<?php

namespace App\Http\Controllers;

use App\Models\Psu;

class PsuController extends Controller
{
    // GET /psus
    public function index()
    {
        return Psu::with('component.brand')->get()->map(function ($psu) {
            return [
                'id' => $psu->id,
                'component_id' => $psu->component_id,
                'name' => $psu->component->name ?? '',
                'brand' => $psu->component->brand->name ?? '',
                'price' => $psu->component->price ?? '',
                'img_url' => $psu->component->img_url ?? '',
                'wattage' => $psu->wattage,
                'certification' => $psu->certification ?? '',
                'modular' => $psu->modular,
                'form_factor' => $psu->form_factor ?? '',
            ];
        })->values();
    }

    // GET /psus/{psu}
    public function show(Psu $psu)
    {
        $psu->load('component.brand');

        return response()->json([
            'id' => $psu->id,
            'component_id' => $psu->component_id,
            'name' => $psu->component->name ?? '',
            'brand' => $psu->component->brand->name ?? '',
            'price' => $psu->component->price ?? '',
            'img_url' => $psu->component->img_url ?? '',
            'wattage' => $psu->wattage,
            'certification' => $psu->certification ?? '',
            'modular' => $psu->modular,
            'form_factor' => $psu->form_factor ?? '',
        ]);
    }
}
