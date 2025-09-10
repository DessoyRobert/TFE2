<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Response;

class BrandController extends Controller
{
    // GET /brands (liste toutes les marques)
    public function index()
    {
        return response()->json(Brand::all(), Response::HTTP_OK);
    }

    // GET /brands/{brand} (affiche une marque précise)
    public function show(Brand $brand)
    {
        return response()->json($brand, Response::HTTP_OK);
    }
}
