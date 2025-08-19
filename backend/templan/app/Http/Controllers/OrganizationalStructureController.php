<?php

namespace App\Http\Controllers;

use App\Models\OrganizationalStructure;
use App\Models\User;
use App\Models\Department;
use App\Models\JobPosition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class OrganizationalStructureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = OrganizationalStructure::query();

        // Filter by active status if requested
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->get('department_id'));
        }

        // Filter by job position
        if ($request->has('job_position_id')) {
            $query->where('job_position_id', $request->get('job_position_id'));
        }

        // Filter by employment type
        if ($request->has('employment_type')) {
            $query->where('employment_type', $request->get('employment_type'));
        }

        // Filter by manager
        if ($request->has('manager_id')) {
            $query->where('manager_id', $request->get('manager_id'));
        }

        // Filter current structures only
        if ($request->boolean('current_only')) {
            $query->current();
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('department', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('jobPosition', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        // Load relationships
        $query->with([
            'user:id,name,email',
            'department:id,name,code',
            'jobPosition:id,title,code',
            'manager:id,name,email',
            'substitute:id,name,email'
        ]);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $structures = $query->paginate($perPage);

        return response()->json($structures);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'job_position_id' => 'required|exists:job_positions,id',
            'manager_id' => 'nullable|exists:users,id',
            'substitute_id' => 'nullable|exists:users,id',
            'employment_type' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'salary' => 'nullable|numeric|min:0',
            'cost_center' => 'nullable|string|max:100',
            'reporting_structure' => 'nullable|array',
            'permissions' => 'nullable|array',
            'external_employee_id' => 'nullable|string|max:100',
            'active' => 'boolean'
        ]);

        // Check if user already has an active structure
        $existingActive = OrganizationalStructure::where('user_id', $validated['user_id'])
                                                 ->where('active', true)
                                                 ->exists();

        if ($existingActive && ($validated['active'] ?? true)) {
            return response()->json([
                'message' => 'Este usuário já possui uma estrutura organizacional ativa. Desative a atual antes de criar uma nova.'
            ], 422);
        }

        $structure = OrganizationalStructure::create($validated);
        $structure->load([
            'user:id,name,email',
            'department:id,name,code',
            'jobPosition:id,title,code',
            'manager:id,name,email',
            'substitute:id,name,email'
        ]);

        return response()->json([
            'message' => 'Estrutura organizacional criada com sucesso.',
            'data' => $structure
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrganizationalStructure $organizationalStructure): JsonResponse
    {
        $organizationalStructure->load([
            'user:id,name,email',
            'department:id,name,code',
            'jobPosition:id,title,code,description',
            'manager:id,name,email',
            'substitute:id,name,email'
        ]);

        return response()->json($organizationalStructure);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrganizationalStructure $organizationalStructure): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'job_position_id' => 'required|exists:job_positions,id',
            'manager_id' => 'nullable|exists:users,id',
            'substitute_id' => 'nullable|exists:users,id',
            'employment_type' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'salary' => 'nullable|numeric|min:0',
            'cost_center' => 'nullable|string|max:100',
            'reporting_structure' => 'nullable|array',
            'permissions' => 'nullable|array',
            'external_employee_id' => 'nullable|string|max:100',
            'active' => 'boolean'
        ]);

        // Check if changing user and new user already has an active structure
        if ($validated['user_id'] !== $organizationalStructure->user_id) {
            $existingActive = OrganizationalStructure::where('user_id', $validated['user_id'])
                                                     ->where('active', true)
                                                     ->where('id', '!=', $organizationalStructure->id)
                                                     ->exists();

            if ($existingActive && ($validated['active'] ?? true)) {
                return response()->json([
                    'message' => 'O usuário selecionado já possui uma estrutura organizacional ativa.'
                ], 422);
            }
        }

        $organizationalStructure->update($validated);
        $organizationalStructure->load([
            'user:id,name,email',
            'department:id,name,code',
            'jobPosition:id,title,code',
            'manager:id,name,email',
            'substitute:id,name,email'
        ]);

        return response()->json([
            'message' => 'Estrutura organizacional atualizada com sucesso.',
            'data' => $organizationalStructure
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrganizationalStructure $organizationalStructure): JsonResponse
    {
        $organizationalStructure->delete();

        return response()->json([
            'message' => 'Estrutura organizacional excluída com sucesso.'
        ]);
    }

    /**
     * Get organizational structures by user
     */
    public function byUser(User $user): JsonResponse
    {
        $structures = $user->organizationalStructures()
                           ->with([
                               'department:id,name,code',
                               'jobPosition:id,title,code',
                               'manager:id,name,email',
                               'substitute:id,name,email'
                           ])
                           ->orderBy('start_date', 'desc')
                           ->get();

        return response()->json($structures);
    }

    /**
     * Get organizational structures by department
     */
    public function byDepartment(Department $department): JsonResponse
    {
        $structures = $department->organizationalStructures()
                                 ->with([
                                     'user:id,name,email',
                                     'jobPosition:id,title,code',
                                     'manager:id,name,email'
                                 ])
                                 ->where('active', true)
                                 ->get();

        return response()->json($structures);
    }

    /**
     * Get current organizational structure for a user
     */
    public function currentByUser(User $user): JsonResponse
    {
        $structure = $user->organizationalStructures()
                          ->current()
                          ->with([
                              'department:id,name,code',
                              'jobPosition:id,title,code,description',
                              'manager:id,name,email',
                              'substitute:id,name,email'
                          ])
                          ->first();

        if (!$structure) {
            return response()->json([
                'message' => 'Usuário não possui estrutura organizacional ativa.'
            ], 404);
        }

        return response()->json($structure);
    }

    /**
     * Toggle organizational structure status
     */
    public function toggleStatus(OrganizationalStructure $organizationalStructure): JsonResponse
    {
        // If activating, check if user already has an active structure
        if (!$organizationalStructure->active) {
            $existingActive = OrganizationalStructure::where('user_id', $organizationalStructure->user_id)
                                                     ->where('active', true)
                                                     ->where('id', '!=', $organizationalStructure->id)
                                                     ->exists();

            if ($existingActive) {
                return response()->json([
                    'message' => 'Este usuário já possui uma estrutura organizacional ativa. Desative a atual antes de ativar esta.'
                ], 422);
            }
        }

        $organizationalStructure->update(['active' => !$organizationalStructure->active]);
        $organizationalStructure->load([
            'user:id,name,email',
            'department:id,name,code',
            'jobPosition:id,title,code',
            'manager:id,name,email'
        ]);

        $status = $organizationalStructure->active ? 'ativada' : 'desativada';
        
        return response()->json([
            'message' => "Estrutura organizacional {$status} com sucesso.",
            'data' => $organizationalStructure
        ]);
    }

    /**
     * Get organizational chart data
     */
    public function organizationalChart(Request $request): JsonResponse
    {
        $departmentId = $request->get('department_id');
        
        $query = OrganizationalStructure::current()
                                        ->with([
                                            'user:id,name,email',
                                            'department:id,name',
                                            'jobPosition:id,title',
                                            'manager:id,name'
                                        ]);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $structures = $query->get();

        // Build hierarchical structure
        $chart = $this->buildHierarchy($structures);

        return response()->json($chart);
    }

    /**
     * Build hierarchical structure for organizational chart
     */
    private function buildHierarchy($structures)
    {
        $hierarchy = [];
        $indexed = [];

        // Index all structures by user_id
        foreach ($structures as $structure) {
            $indexed[$structure->user_id] = [
                'id' => $structure->id,
                'user' => $structure->user,
                'department' => $structure->department,
                'job_position' => $structure->jobPosition,
                'manager_id' => $structure->manager_id,
                'children' => []
            ];
        }

        // Build hierarchy
        foreach ($indexed as $userId => $item) {
            if ($item['manager_id'] && isset($indexed[$item['manager_id']])) {
                $indexed[$item['manager_id']]['children'][] = &$indexed[$userId];
            } else {
                $hierarchy[] = &$indexed[$userId];
            }
        }

        return $hierarchy;
    }

    /**
     * Get team members for a manager
     */
    public function teamMembers(User $manager): JsonResponse
    {
        $teamMembers = OrganizationalStructure::where('manager_id', $manager->id)
                                              ->where('active', true)
                                              ->with([
                                                  'user:id,name,email',
                                                  'department:id,name',
                                                  'jobPosition:id,title'
                                              ])
                                              ->get();

        return response()->json($teamMembers);
    }
}
