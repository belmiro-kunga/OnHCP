<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Department::query();

        // Filter by active status if requested
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Load relationships and counts
        $query->with('manager:id,name,email')
              ->withCount('users');

        // Pagination
        $perPage = $request->get('per_page', 15);
        $departments = $query->orderBy('name')->paginate($perPage);

        return response()->json($departments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string|max:500',
            'code' => 'nullable|string|max:10|unique:departments,code',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean'
        ]);

        $department = Department::create($validated);
        $department->load('manager:id,name,email');
        $department->loadCount('users');

        return response()->json([
            'message' => 'Departamento criado com sucesso.',
            'department' => $department
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department): JsonResponse
    {
        $department->load('manager:id,name,email', 'users:id,name,email,role_id')
                   ->loadCount('users');

        return response()->json($department);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'description' => 'nullable|string|max:500',
            'code' => ['nullable', 'string', 'max:10', Rule::unique('departments')->ignore($department->id)],
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean'
        ]);

        $department->update($validated);
        $department->load('manager:id,name,email');
        $department->loadCount('users');

        return response()->json([
            'message' => 'Departamento atualizado com sucesso.',
            'department' => $department
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department): JsonResponse
    {
        // Check if department has users assigned
        if ($department->users()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir um departamento que possui usuários associados.'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'message' => 'Departamento excluído com sucesso.'
        ]);
    }

    /**
     * Get all active departments for dropdown/select options.
     */
    public function options(): JsonResponse
    {
        $departments = Department::active()
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return response()->json($departments);
    }

    /**
     * Toggle the active status of a department.
     */
    public function toggleStatus(Department $department): JsonResponse
    {
        $department->update(['is_active' => !$department->is_active]);
        $department->load('manager:id,name,email');
        $department->loadCount('users');

        $status = $department->is_active ? 'ativado' : 'desativado';
        
        return response()->json([
            'message' => "Departamento {$status} com sucesso.",
            'department' => $department
        ]);
    }

    /**
     * Get potential managers (users) for departments.
     */
    public function potentialManagers(): JsonResponse
    {
        $users = User::select('id', 'name', 'email')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }
}