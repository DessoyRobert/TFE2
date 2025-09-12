<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComponentType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class ComponentTypeController extends Controller
{
    
    // GET /admin/component-types
    public function index()
    {
        return Inertia::render('Admin/ComponentTypes/Index', [
            'componentTypes' => ComponentType::orderBy('id', 'desc')->paginate(15)
        ]);
    }

    // GET /admin/component-types/{component_type}/edit
    public function edit(ComponentType $componentType)
    {
        return Inertia::render('Admin/ComponentTypes/Edit', [
            'componentType' => $componentType
        ]);
    }

    // POST /admin/component-types
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:component_types,name|max:255',
        ]);

        $type = ComponentType::create($validated);

        return back()->with('success', 'Type de composant ajouté !');
    }

    // PUT/PATCH /admin/component-types/{component_type}
    public function update(Request $request, ComponentType $componentType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:component_types,name,' . $componentType->id,
        ]);

        $componentType->update($validated);

        return back()->with('success', 'Type de composant mis à jour !');
    }

    // DELETE /admin/component-types/{component_type}
    public function destroy(ComponentType $componentType)
    {
        $componentType->delete();

        return back()->with('success', 'Type de composant supprimé !');
    }
}