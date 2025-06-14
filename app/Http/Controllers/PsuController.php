<?php

namespace App\Http\Controllers;

use App\Models\Psu;

class PsuController extends Controller
{
    public function index()
    {
        $collection = Psu::with('component.brand')->paginate(15);
    $collection->getCollection()->transform(function ($psu) {
            return [
                'id' => $psu->component_id,
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
    return $collection;
    }

    public function show(Psu $psu)
    {
        $psu->load('component.brand');

        return response()->json([
            'id' => $psu->component_id,
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
