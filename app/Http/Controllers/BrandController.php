<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BrandController extends Controller
{
    public function index()
    {
        // Si tu veux charger une relation (ex: 'products'), ajoute ->with('products')
        return response()->json(Brand::all(), Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:brands,name|max:255',
        ]);

        $brand = Brand::create($validated);

        return response()->json($brand, Response::HTTP_CREATED);
    }

    public function show(Brand $brand)
    {
        return response()->json($brand, Response::HTTP_OK);
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
        ]);

        $brand->update($validated);

        return response()->json($brand, Response::HTTP_OK);
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
