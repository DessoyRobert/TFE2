<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    // GET /admin/categories
    public function index()
    {
        return Inertia::render('Admin/Categories/Index', [
            'categories' => \App\Models\Category::orderBy('id', 'desc')->paginate(15)
        ]);
    }


    // GET /admin/categories/create
    public function create()
    {
        return Inertia::render('Admin/Categories/Create');
    }

    // POST /admin/categories
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:categories,name|max:255',
        ]);

        Category::create($data);

        return redirect()->route('admin.categories.index');
    }

    // GET /admin/categories/{category}/edit
    public function edit(Category $category)
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category
        ]);
    }

    // PUT/PATCH /admin/categories/{category}
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($data);

        return redirect()->route('admin.categories.index');
    }

    // DELETE /admin/categories/{category}
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index');
    }
}
