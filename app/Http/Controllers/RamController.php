<?php

namespace App\Http\Controllers;

use App\Models\Ram;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RamController extends Controller
{
    public function index()
    {
        return response()->json(
            Ram::with('brand')->get(),
            Response::HTTP_OK
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'type'     => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'speed'    => 'required|integer|min:1',
            'modules'  => 'required|integer|min:1',
            'price'    => 'required|numeric|min:0',
        ]);

        $ram = Ram::create($validated);

        return response()->json($ram, Response::HTTP_CREATED);
    }

    public function show(Ram $ram)
    {
        $ram->load('brand');

        return response()->json($ram, Response::HTTP_OK);
    }

    public function update(Request $request, Ram $ram)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'type'     => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'speed'    => 'required|integer|min:1',
            'modules'  => 'required|integer|min:1',
            'price'    => 'required|numeric|min:0',
        ]);

        $ram->update($validated);

        return response()->json($ram, Response::HTTP_OK);
    }

    public function destroy(Ram $ram)
    {
        $ram->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
