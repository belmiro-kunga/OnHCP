<?php

namespace App\Http\Controllers;

use App\Models\OnboardingRoadmap;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OnboardingRoadmapController extends Controller
{
    /**
     * Display a listing of the roadmaps.
     */
    public function index(Request $request): JsonResponse
    {
        $query = OnboardingRoadmap::with(['department:id,name', 'creator:id,name'])
            ->select('id', 'name', 'description', 'department_id', 'estimated_duration_days', 'status', 'created_by', 'created_at');

        // Filter by department
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search by name or description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roadmaps = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($roadmaps);
    }

    /**
     * Store a newly created roadmap.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'steps' => 'required|array|min:1',
            'steps.*.id' => 'required|string',
            'steps.*.title' => 'required|string|max:255',
            'steps.*.description' => 'nullable|string',
            'steps.*.type' => 'required|in:task,document,training,meeting',
            'steps.*.estimated_hours' => 'nullable|integer|min:1',
            'steps.*.is_mandatory' => 'boolean',
            'estimated_duration_days' => 'required|integer|min:1',
            'status' => 'in:draft,active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $roadmap = OnboardingRoadmap::create([
            'name' => $request->name,
            'description' => $request->description,
            'department_id' => $request->department_id,
            'steps' => $request->steps,
            'estimated_duration_days' => $request->estimated_duration_days,
            'status' => $request->status ?? 'draft',
            'created_by' => Auth::id()
        ]);

        $roadmap->load(['department:id,name', 'creator:id,name']);

        return response()->json([
            'message' => 'Roadmap created successfully',
            'roadmap' => $roadmap
        ], 201);
    }

    /**
     * Display the specified roadmap.
     */
    public function show(OnboardingRoadmap $roadmap): JsonResponse
    {
        $roadmap->load(['department:id,name', 'creator:id,name']);
        
        return response()->json($roadmap);
    }

    /**
     * Update the specified roadmap.
     */
    public function update(Request $request, OnboardingRoadmap $roadmap): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'sometimes|required|exists:departments,id',
            'steps' => 'sometimes|required|array|min:1',
            'steps.*.id' => 'required|string',
            'steps.*.title' => 'required|string|max:255',
            'steps.*.description' => 'nullable|string',
            'steps.*.type' => 'required|in:task,document,training,meeting',
            'steps.*.estimated_hours' => 'nullable|integer|min:1',
            'steps.*.is_mandatory' => 'boolean',
            'estimated_duration_days' => 'sometimes|required|integer|min:1',
            'status' => 'in:draft,active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $roadmap->update($request->only([
            'name', 'description', 'department_id', 'steps', 
            'estimated_duration_days', 'status'
        ]));

        $roadmap->load(['department:id,name', 'creator:id,name']);

        return response()->json([
            'message' => 'Roadmap updated successfully',
            'roadmap' => $roadmap
        ]);
    }

    /**
     * Remove the specified roadmap.
     */
    public function destroy(OnboardingRoadmap $roadmap): JsonResponse
    {
        // Check if roadmap is being used
        if ($roadmap->userProgress()->exists()) {
            return response()->json([
                'message' => 'Cannot delete roadmap that is being used by users'
            ], 422);
        }

        $roadmap->delete();

        return response()->json([
            'message' => 'Roadmap deleted successfully'
        ]);
    }

    /**
     * Get departments for dropdown.
     */
    public function departments(): JsonResponse
    {
        $departments = Department::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($departments);
    }

    /**
     * Duplicate a roadmap.
     */
    public function duplicate(OnboardingRoadmap $roadmap): JsonResponse
    {
        $newRoadmap = $roadmap->replicate();
        $newRoadmap->name = $roadmap->name . ' (Copy)';
        $newRoadmap->status = 'draft';
        $newRoadmap->created_by = Auth::id();
        $newRoadmap->save();

        $newRoadmap->load(['department:id,name', 'creator:id,name']);

        return response()->json([
            'message' => 'Roadmap duplicated successfully',
            'roadmap' => $newRoadmap
        ], 201);
    }
}