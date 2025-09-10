<?php

namespace App\Http\Controllers;

use App\Models\Motherboard;

class MotherboardController extends Controller
{
    public function index()
    {
        $collection = Motherboard::with(['component.brand', 'component.images'])->paginate(15);
        $collection->getCollection()->transform(function ($mb) {
            return [
                'id' => $mb->component_id,
                'component_id' => $mb->component_id,
                'name' => $mb->component->name ?? '',
                'brand' => $mb->component->brand->name ?? '',
                'price' => $mb->component->price ?? '',
                'img_url' => optional($mb->component->images->first())->url
                    ?? $mb->component->img_url
                    ?? '/images/default.png',
                'socket' => $mb->socket,
                'chipset' => $mb->chipset,
                'form_factor' => $mb->form_factor,
                'ram_type' => $mb->ram_type,
                'max_ram' => $mb->max_ram,
            ];
        })->values();
        return $collection;
    }

    public function show(Motherboard $motherboard)
    {
        $motherboard->load(['component.brand', 'component.images']);

        return response()->json([
            'id' => $motherboard->component_id,
            'component_id' => $motherboard->component_id,
            'name' => $motherboard->component->name ?? '',
            'brand' => $motherboard->component->brand->name ?? '',
            'price' => $motherboard->component->price ?? '',
            'img_url' => optional($motherboard->component->images->first())->url
                ?? $motherboard->component->img_url
                ?? '/images/default.png',
            'socket' => $motherboard->socket,
            'chipset' => $motherboard->chipset,
            'form_factor' => $motherboard->form_factor,
            'ram_type' => $motherboard->ram_type,
            'max_ram' => $motherboard->max_ram,
        ]);
    }
}
