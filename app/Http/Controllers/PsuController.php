<?php

namespace App\Http\Controllers;

use App\Models\Psu;

class PsuController extends Controller
{ protected string $fallbackImage =
        'https://res.cloudinary.com/djllwl8c0/image/upload/v1753292540/Logo-JarvisTech-PNG-normalsansfond_pgxlrj.png';

    public function index()
    {
        $collection = Psu::with(['component.brand', 'component.images'])->paginate(15);
        $collection->getCollection()->transform(function ($psu) {
            return [
                'id' => $psu->component_id,
                'component_id' => $psu->component_id,
                'name' => $psu->component->name ?? '',
                'brand' => $psu->component->brand->name ?? '',
                'price' => $psu->component->price ?? '',
                'img_url' => optional($psu->component->images->first())->url
                    ?? $psu->component->img_url
                    ?? $this->fallbackImage,
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
        $psu->load(['component.brand', 'component.images']);

        return response()->json([
            'id' => $psu->component_id,
            'component_id' => $psu->component_id,
            'name' => $psu->component->name ?? '',
            'brand' => $psu->component->brand->name ?? '',
            'price' => $psu->component->price ?? '',
            'img_url' => optional($psu->component->images->first())->url
                ?? $psu->component->img_url
                ?? $this->fallbackImage,
            'wattage' => $psu->wattage,
            'certification' => $psu->certification ?? '',
            'modular' => $psu->modular,
            'form_factor' => $psu->form_factor ?? '',
        ]);
    }
}
