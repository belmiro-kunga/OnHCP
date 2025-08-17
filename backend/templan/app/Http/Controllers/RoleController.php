<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Role::query();

        // Filter by active status if requested
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Load users count
        $query->withCount('users');

        // Pagination
        $perPage = $request->get('per_page', 15);
        $roles = $query->orderBy('name')->paginate($perPage);

        return response()->json($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean'
        ]);

        $role = Role::create($validated);
        $role->loadCount('users');

        return response()->json([
            'message' => 'Cargo criado com sucesso.',
            'role' => $role
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): JsonResponse
    {
        $role->loadCount('users');
        $role->load('users:id,name,email');

        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean'
        ]);

        $role->update($validated);
        $role->loadCount('users');

        return response()->json([
            'message' => 'Cargo atualizado com sucesso.',
            'role' => $role
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): JsonResponse
    {
        // Check if role has users assigned
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir um cargo que possui usuários associados.'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Cargo excluído com sucesso.'
        ]);
    }

    /**
     * Get all active roles for dropdown/select options.
     */
    public function options(): JsonResponse
    {
        $roles = Role::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($roles);
    }

    /**
     * Toggle the active status of a role.
     */
    public function toggleStatus(Role $role): JsonResponse
    {
        $role->update(['is_active' => !$role->is_active]);
        $role->loadCount('users');

        $status = $role->is_active ? 'ativado' : 'desativado';
        
        return response()->json([
            'message' => "Cargo {$status} com sucesso.",
            'role' => $role
        ]);
    }
}