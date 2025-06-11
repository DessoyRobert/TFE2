<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
class BrandController extends Controller
{
    // GET /admin/brands (optionnel pour admin panel)
    public function index()
    {
        return Inertia::render('Admin/Brands/Index', [
        'brands' => Brand::all(['id', 'name'])
    ]);
    }
    public function edit(Brand $brand)
    {
        return \Inertia\Inertia::render('Admin/Brands/Edit', [
            'brand' => $brand
        ]);
    }
    // POST /admin/brands
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:brands,name|max:255',
        ]);

        $brand = Brand::create($validated);

        return response()->json($brand, Response::HTTP_CREATED);
    }

    // GET /admin/brands/{brand} (optionnel pour admin panel)
    public function show(Brand $brand)
    {
        return response()->json($brand, Response::HTTP_OK);
    }

    // PUT/PATCH /admin/brands/{brand}
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
        ]);

        $brand->update($validated);

        return response()->json($brand, Response::HTTP_OK);
    }

    // DELETE /admin/brands/{brand}
    public function destroy(Brand $brand)
    {
        $brand->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
