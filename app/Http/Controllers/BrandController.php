<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        return Brand::all();
    }

    public function create()
    {
        
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:brands,name|max:255',
        ]);

        $brand = Brand::create([
            'name' => $request->name,
        ]);

        return response()->json($brand, 201);
    }

    public function show(Brand $brand)
    {
        return $brand;
    }

    public function edit(Brand $brand)
    {
        // Si tu utilises une vue pour éditer, sinon inutile en API
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|unique:brands,name,' . $brand->id,
        ]);

        $brand->update([
            'name' => $request->name,
        ]);

        return response()->json($brand);
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return response()->json(null, 204);
    }
}
