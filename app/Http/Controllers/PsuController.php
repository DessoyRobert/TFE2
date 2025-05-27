<?php

namespace App\Http\Controllers;

use App\Models\Psu;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PsuController extends Controller
{
    public function index()
    {
        return response()->json(
            Psu::with('brand')->get(),
            Response::HTTP_OK
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'brand_id'   => 'required|exists:brands,id',
            'wattage'    => 'required|integer|min:100',
            'efficiency' => 'required|string|max:50',
            'modular'    => 'required|boolean',
            'price'      => 'required|numeric|min:0',
        ]);

        $psu = Psu::create($validated);

        return response()->json($psu, Response::HTTP_CREATED);
    }

    public function show(Psu $psu)
    {
        $psu->load('brand');

        return response()->json($psu, Response::HTTP_OK);
    }

    public function update(Request $request, Psu $psu)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'brand_id'   => 'required|exists:brands,id',
            'wattage'    => 'required|integer|min:100',
            'efficiency' => 'required|string|max:50',
            'modular'    => 'required|boolean',
            'price'      => 'required|numeric|min:0',
        ]);

        $psu->update($validated);

        return response()->json($psu, Response::HTTP_OK);
    }

    public function destroy(Psu $psu)
    {
        $psu->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
