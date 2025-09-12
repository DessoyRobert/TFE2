<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComponentResource;
use App\Models\Component;
use Illuminate\Support\Facades\DB;

class ComponentController extends Controller
{
    
    /** renvoie LIKE (MySQL) ou ILIKE (Postgres) */
    protected function likeOp(): string
    {
        return DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
    }

    public function index()
    {
        $op = $this->likeOp();

        // charge brand & type pour éviter N+1
        $q = Component::query()->with(['brand','type']);

        // filtres simples (case-insensitive portable)
        if ($s = request()->query('search')) {
            $needle = '%'.mb_strtolower($s).'%';

            $q->where(function ($qq) use ($op, $needle) {
                $qq->whereRaw("LOWER(components.name) {$op} ?", [$needle])
                   ->orWhereHas('brand', function ($b) use ($op, $needle) {
                       $b->whereRaw("LOWER(brands.name) {$op} ?", [$needle]);
                   })
                   ->orWhereHas('type', function ($t) use ($op, $needle) {
                       $t->whereRaw("LOWER(component_types.name) {$op} ?", [$needle]);
                   });
            });
        }

        // tri simple
        $sortBy = in_array(request('sortBy'), ['id','name','price'], true) ? request('sortBy') : 'id';
        $dir    = request()->boolean('sortDesc', true) ? 'desc' : 'asc';
        $q->orderBy($sortBy, $dir);

        $perPage   = (int) (request('per_page') ?? 15);
        $paginated = $q->paginate($perPage)->withQueryString();

        return ComponentResource::collection($paginated);
    }

    public function show(Component $component)
    {
        $component->load(['brand','type']);
        return new ComponentResource($component);
    }
}
