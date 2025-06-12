<?php

namespace App\Http\Controllers;

use App\Models\Motherboard;

class MotherboardController extends Controller
{
    public function index()
    {
        return Motherboard::with('component.brand')->get()->map(function ($mb) {
            return [
                'id' => $mb->component_id,
                'component_id' => $mb->component_id,
                'name' => $mb->component->name ?? '',
                'brand' => $mb->component->brand->name ?? '',
                'price' => $mb->component->price ?? '',
                'img_url' => $mb->component->img_url ?? '',
                'socket' => $mb->socket,
                'chipset' => $mb->chipset,
                'form_factor' => $mb->form_factor,
                'ram_type' => $mb->ram_type,
                'max_ram' => $mb->max_ram,
            ];
        })->values();
    }

    public function show(Motherboard $motherboard)
    {
        $motherboard->load('component.brand');

        return response()->json([
            'id' => $motherboard->component_id,
            'component_id' => $motherboard->component_id,
            'name' => $motherboard->component->name ?? '',
            'brand' => $motherboard->component->brand->name ?? '',
            'price' => $motherboard->component->price ?? '',
            'img_url' => $motherboard->component->img_url ?? '',
            'socket' => $motherboard->socket,
            'chipset' => $motherboard->chipset,
            'form_factor' => $motherboard->form_factor,
            'ram_type' => $motherboard->ram_type,
            'max_ram' => $motherboard->max_ram,
        ]);
    }
}
