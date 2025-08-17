<?php

namespace App\Http\Controllers;

use App\Models\RoleMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleMappingController extends Controller
{
    public function index(Request $request)
    {
        $items = RoleMapping::with(['role','department'])
            ->orderBy('priority')
            ->get();
        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'department_id' => 'nullable|exists:departments,id',
            'job_title' => 'nullable|string|max:255',
            'ad_group' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'priority' => 'nullable|integer',
            'active' => 'boolean',
        ]);
        $validator->validate();
        $rm = RoleMapping::create([
            'department_id' => $data['department_id'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'ad_group' => $data['ad_group'] ?? null,
            'role_id' => $data['role_id'],
            'priority' => $data['priority'] ?? 100,
            'active' => $data['active'] ?? true,
        ]);
        return response()->json(['data' => $rm->load(['role','department'])], 201);
    }

    public function update(Request $request, RoleMapping $roleMapping)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'department_id' => 'nullable|exists:departments,id',
            'job_title' => 'nullable|string|max:255',
            'ad_group' => 'nullable|string|max:255',
            'role_id' => 'sometimes|required|exists:roles,id',
            'priority' => 'nullable|integer',
            'active' => 'boolean',
        ]);
        $validator->validate();

        foreach (['department_id','job_title','ad_group','role_id','priority','active'] as $k) {
            if (array_key_exists($k, $data)) $roleMapping->$k = $data[$k];
        }
        $roleMapping->save();
        return response()->json(['data' => $roleMapping->load(['role','department'])]);
    }

    public function destroy(RoleMapping $roleMapping)
    {
        $roleMapping->delete();
        return response()->json(['data' => true]);
    }

    public function reorder(Request $request)
    {
        $items = (array)($request->input('items', [])); // [{id, priority}]
        foreach ($items as $it) {
            if (!empty($it['id'])) {
                RoleMapping::where('id', $it['id'])->update(['priority' => (int)($it['priority'] ?? 100)]);
            }
        }
        return response()->json(['data' => true]);
    }
}
