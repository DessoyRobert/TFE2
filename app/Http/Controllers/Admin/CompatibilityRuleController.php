<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompatibilityRule;
use App\Models\ComponentType;
use Illuminate\Http\Request;

class CompatibilityRuleController extends Controller
{
    // GET /admin/compatibility-rules
    public function index()
    {
        $rules = CompatibilityRule::with(['componentTypeA', 'componentTypeB'])->get();
        return inertia('Admin/CompatibilityRules/Index', [
            'rules' => $rules,
        ]);
    }

    // GET /admin/compatibility-rules/create
    public function create()
    {
        return inertia('Admin/CompatibilityRules/Create', [
            'componentTypes' => ComponentType::all(),
        ]);
    }

    // POST /admin/compatibility-rules
    public function store(Request $request)
    {
        $data = $request->validate([
            'component_type_a_id' => 'required|exists:component_types,id',
            'field_a'             => 'required|string',
            'operator'            => 'required|string',
            'field_b'             => 'required|string',
            'component_type_b_id' => 'required|exists:component_types,id',
            'rule_type'           => 'required|in:hard,soft',
            'description'         => 'nullable|string',
        ]);

        CompatibilityRule::create($data);

        return redirect()->route('admin.compatibility-rules.index')->with('success', 'Règle ajoutée');
    }

    // GET /admin/compatibility-rules/{id}/edit
    public function edit(CompatibilityRule $compatibilityRule)
    {
        return inertia('Admin/CompatibilityRules/Edit', [
            'rule' => $compatibilityRule->load(['componentTypeA', 'componentTypeB']),
            'componentTypes' => ComponentType::all(),
        ]);
    }

    // PUT /admin/compatibility-rules/{id}
    public function update(Request $request, CompatibilityRule $compatibilityRule)
    {
        $data = $request->validate([
            'component_type_a_id' => 'required|exists:component_types,id',
            'field_a'             => 'required|string',
            'operator'            => 'required|string',
            'field_b'             => 'required|string',
            'component_type_b_id' => 'required|exists:component_types,id',
            'rule_type'           => 'required|in:hard,soft',
            'description'         => 'nullable|string',
        ]);

        $compatibilityRule->update($data);

        return redirect()->route('admin.compatibility-rules.index')->with('success', 'Règle mise à jour');
    }

    // DELETE /admin/compatibility-rules/{id}
    public function destroy(CompatibilityRule $compatibilityRule)
    {
        $compatibilityRule->delete();

        return redirect()->route('admin.compatibility-rules.index')->with('success', 'Règle supprimée');
    }
}
