<?php

namespace App\Http\Controllers;

use App\Models\Motherboard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MotherboardController extends Controller
{
    public function index()
    {
        return Motherboard::with('component')->get()->map(function ($mb) {
            return [
                'id' => $mb->id,
                'component_id' => $mb->component_id,
                'name' => $mb->component->name,
                'price' => $mb->component->price,
                'img_url' => $mb->component->img_url,
                'socket' => $mb->socket,
                'chipset' => $mb->chipset,
            ];
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'brand_id'    => 'required|exists:brands,id',
            'socket'      => 'required|string|max:50',
            'chipset'     => 'required|string|max:100',
            'form_factor' => 'required|string|max:20',
            'ram_type'    => 'required|string|max:10',
            'max_ram'     => 'required|integer|min:1',
            'price'       => 'required|numeric|min:0',
        ]);

        $motherboard = Motherboard::create($validated);

        return response()->json($motherboard, Response::HTTP_CREATED);
    }

    public function show(Motherboard $motherboard)
    {
        $motherboard->load('brand');

        return response()->json($motherboard, Response::HTTP_OK);
    }

    public function update(Request $request, Motherboard $motherboard)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'brand_id'    => 'required|exists:brands,id',
            'socket'      => 'required|string|max:50',
            'chipset'     => 'required|string|max:100',
            'form_factor' => 'required|string|max:20',
            'ram_type'    => 'required|string|max:10',
            'max_ram'     => 'required|integer|min:1',
            'price'       => 'required|numeric|min:0',
        ]);

        $motherboard->update($validated);

        return response()->json($motherboard, Response::HTTP_OK);
    }

    public function destroy(Motherboard $motherboard)
    {
        $motherboard->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
