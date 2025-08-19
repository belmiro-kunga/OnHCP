<?php

namespace App\Http\Controllers;

use App\Models\JobPosition;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class JobPositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobPosition::query();

        // Filter by active status if requested
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->get('department_id'));
        }

        // Filter by level
        if ($request->has('level')) {
            $query->where('level', $request->get('level'));
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->get('category'));
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('hr_code', 'like', "%{$search}%");
            });
        }

        // Load relationships and counts
        $query->with(['department:id,name'])
              ->withCount('organizationalStructures');

        // Pagination
        $perPage = $request->get('per_page', 15);
        $jobPositions = $query->paginate($perPage);

        return response()->json($jobPositions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:job_positions,code',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'level' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'requirements' => 'nullable|array',
            'responsibilities' => 'nullable|array',
            'benefits' => 'nullable|array',
            'external_id' => 'nullable|string|max:100',
            'hr_code' => 'nullable|string|max:100',
            'active' => 'boolean'
        ]);

        $jobPosition = JobPosition::create($validated);
        $jobPosition->load('department:id,name');
        $jobPosition->loadCount('organizationalStructures');

        return response()->json([
            'message' => 'Cargo criado com sucesso.',
            'data' => $jobPosition
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(JobPosition $jobPosition): JsonResponse
    {
        $jobPosition->load([
            'department:id,name,code',
            'organizationalStructures.user:id,name,email'
        ])->loadCount('organizationalStructures');

        return response()->json($jobPosition);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobPosition $jobPosition): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('job_positions')->ignore($jobPosition->id)],
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'level' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'requirements' => 'nullable|array',
            'responsibilities' => 'nullable|array',
            'benefits' => 'nullable|array',
            'external_id' => 'nullable|string|max:100',
            'hr_code' => 'nullable|string|max:100',
            'active' => 'boolean'
        ]);

        $jobPosition->update($validated);
        $jobPosition->load('department:id,name');
        $jobPosition->loadCount('organizationalStructures');

        return response()->json([
            'message' => 'Cargo atualizado com sucesso.',
            'data' => $jobPosition
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobPosition $jobPosition): JsonResponse
    {
        // Check if job position has active organizational structures
        $activeStructures = $jobPosition->organizationalStructures()->where('active', true)->count();
        
        if ($activeStructures > 0) {
            return response()->json([
                'message' => 'Não é possível excluir este cargo pois existem estruturas organizacionais ativas associadas.'
            ], 422);
        }

        $jobPosition->delete();

        return response()->json([
            'message' => 'Cargo excluído com sucesso.'
        ]);
    }

    /**
     * Get job positions options for select inputs
     */
    public function options(Request $request): JsonResponse
    {
        $query = JobPosition::active();
        
        if ($request->has('department_id')) {
            $query->where('department_id', $request->get('department_id'));
        }
        
        $jobPositions = $query->select('id', 'title', 'code', 'department_id')
                             ->with('department:id,name')
                             ->orderBy('title')
                             ->get();

        return response()->json($jobPositions);
    }

    /**
     * Toggle job position status
     */
    public function toggleStatus(JobPosition $jobPosition): JsonResponse
    {
        $jobPosition->update(['active' => !$jobPosition->active]);
        $jobPosition->load('department:id,name');
        $jobPosition->loadCount('organizationalStructures');

        $status = $jobPosition->active ? 'ativado' : 'desativado';
        
        return response()->json([
            'message' => "Cargo {$status} com sucesso.",
            'data' => $jobPosition
        ]);
    }

    /**
     * Get job positions by department
     */
    public function byDepartment(Department $department): JsonResponse
    {
        $jobPositions = $department->jobPositions()
                                  ->with('organizationalStructures.user:id,name')
                                  ->withCount('organizationalStructures')
                                  ->get();

        return response()->json($jobPositions);
    }

    /**
     * Get salary ranges for job positions
     */
    public function salaryRanges(): JsonResponse
    {
        $ranges = JobPosition::active()
                            ->whereNotNull('min_salary')
                            ->whereNotNull('max_salary')
                            ->select('id', 'title', 'min_salary', 'max_salary')
                            ->get()
                            ->map(function ($position) {
                                return [
                                    'id' => $position->id,
                                    'title' => $position->title,
                                    'salary_range' => $position->salary_range
                                ];
                            });

        return response()->json($ranges);
    }
}
