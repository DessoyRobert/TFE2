<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
     
    // GET /categories
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // GET /categories/{id}
    public function show($id)
    {
        $category = Category::with('components.brand')->findOrFail($id);
        return response()->json($category);
    }
}
