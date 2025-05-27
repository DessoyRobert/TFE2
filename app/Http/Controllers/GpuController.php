<?php

namespace App\Http\Controllers;

use App\Models\Gpu;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GpuController extends Controller
{
    public function index()
    {
        return response()->json(
            Gpu::with('brand')->get(),
            Response::HTTP_OK
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'chipset'  => 'required|string|max:255',
            'vram'     => 'required|integer|min:1',
            'tdp'      => 'required|integer|min:0',
            'price'    => 'required|numeric|min:0',
        ]);

        $gpu = Gpu::create($validated);

        return response()->json($gpu, Response::HTTP_CREATED);
    }

    public function show(Gpu $gpu)
    {
        $gpu->load('brand');

        return response()->json($gpu, Response::HTTP_OK);
    }

    public function update(Request $request, Gpu $gpu)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'chipset'  => 'required|string|max:255',
            'vram'     => 'required|integer|min:1',
            'tdp'      => 'required|integer|min:0',
            'price'    => 'required|numeric|min:0',
        ]);

        $gpu->update($validated);

        return response()->json($gpu, Response::HTTP_OK);
    }

    public function destroy(Gpu $gpu)
    {
        $gpu->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
