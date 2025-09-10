<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComponentResource;
use App\Models\Component;

class ComponentController extends Controller
{
    public function index()
    {
        // charge brand & type pour éviter N+1
        $q = Component::query()->with(['brand','type']);

        // filtres simples (optionnels)
        if ($s = request()->query('search')) {
            $q->where('name', 'like', "%{$s}%")
              ->orWhereHas('brand', fn($b)=>$b->where('name','ilike',"%{$s}%"))
              ->orWhereHas('type',  fn($t)=>$t->where('name','ilike',"%{$s}%"));
        }

        // tri simple
        $sortBy = in_array(request('sortBy'), ['id','name','price'], true) ? request('sortBy') : 'id';
        $dir    = request()->boolean('sortDesc', true) ? 'desc' : 'asc';
        $q->orderBy($sortBy, $dir);

        $perPage = (int) (request('per_page') ?? 15);
        $paginated = $q->paginate($perPage)->withQueryString();

        return ComponentResource::collection($paginated);
    }

    public function show(Component $component)
    {
        $component->load(['brand','type']);
        return new ComponentResource($component);
    }
}
