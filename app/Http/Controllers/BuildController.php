<?php

namespace App\Http\Controllers;

use App\Models\Build;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BuildController extends Controller
{
    // GET /builds
    public function index(Request $request)
    {
        $search = $request->input('search');
        $builds = Build::with('components.brand')
            ->when($search, fn($q) => $q->where('name', 'ILIKE', "%$search%"))
            ->paginate(15);

        return Inertia::render('Builds/Index', [
            'builds' => $builds,
            'isAdmin' => auth()->check() && auth()->user()->role === 'admin',
        ]);
    }

    // GET /builds/create
    public function create()
    {
        return Inertia::render('Builds/Create');
    }

    // POST /builds
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'img_url' => 'nullable|string',
            'price' => 'nullable|numeric',
            'components' => 'required|array',
            'components.*.component_id' => 'required|integer|exists:components,id',
        ]);

        $build = Build::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'img_url' => $data['img_url'] ?? null,
            'price' => $data['price'] ?? null,
        ]);

        foreach ($data['components'] as $component) {
            $build->components()->attach($component['component_id']);
        }

        return response()->json($build->load('components'), 201);
    }

    // GET /builds/{build}
    public function show(Build $build)
    {
        $build->load('components.brand');

        return Inertia::render('Builds/Show', [
            'build' => $build
        ]);
    }
}
