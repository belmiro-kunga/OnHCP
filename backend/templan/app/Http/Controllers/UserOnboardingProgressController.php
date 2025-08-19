<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OnboardingRoadmap;
use App\Models\UserOnboardingProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserOnboardingProgressController extends Controller
{
    /**
     * Display a listing of user onboarding progress.
     */
    public function index(Request $request): JsonResponse
    {
        $query = UserOnboardingProgress::with([
            'user:id,name,email',
            'roadmap:id,name,estimated_duration_days',
            'assignedBy:id,name'
        ])->select('id', 'user_id', 'roadmap_id', 'progress_percentage', 'status', 
                   'started_at', 'completed_at', 'assigned_by', 'created_at');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by roadmap
        if ($request->has('roadmap_id') && $request->roadmap_id) {
            $query->where('roadmap_id', $request->roadmap_id);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Search by user name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $progress = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($progress);
    }

    /**
     * Assign a roadmap to a user.
     */
    public function assign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'roadmap_id' => 'required|exists:onboarding_roadmaps,id',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user already has this roadmap assigned
        $existingProgress = UserOnboardingProgress::where('user_id', $request->user_id)
            ->where('roadmap_id', $request->roadmap_id)
            ->first();

        if ($existingProgress) {
            return response()->json([
                'message' => 'User already has this roadmap assigned'
            ], 422);
        }

        $progress = UserOnboardingProgress::create([
            'user_id' => $request->user_id,
            'roadmap_id' => $request->roadmap_id,
            'completed_steps' => [],
            'progress_percentage' => 0,
            'status' => 'not_started',
            'notes' => $request->notes,
            'assigned_by' => Auth::id()
        ]);

        $progress->load([
            'user:id,name,email',
            'roadmap:id,name,estimated_duration_days',
            'assignedBy:id,name'
        ]);

        return response()->json([
            'message' => 'Roadmap assigned successfully',
            'progress' => $progress
        ], 201);
    }

    /**
     * Start onboarding for a user.
     */
    public function start(UserOnboardingProgress $progress): JsonResponse
    {
        if ($progress->status !== 'not_started') {
            return response()->json([
                'message' => 'Onboarding has already been started'
            ], 422);
        }

        $progress->start();
        $progress->load([
            'user:id,name,email',
            'roadmap:id,name,estimated_duration_days',
            'assignedBy:id,name'
        ]);

        return response()->json([
            'message' => 'Onboarding started successfully',
            'progress' => $progress
        ]);
    }

    /**
     * Complete a step in the onboarding process.
     */
    public function completeStep(Request $request, UserOnboardingProgress $progress): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'step_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify step exists in roadmap
        $roadmapSteps = $progress->roadmap->steps ?? [];
        $stepExists = collect($roadmapSteps)->contains('id', $request->step_id);

        if (!$stepExists) {
            return response()->json([
                'message' => 'Step not found in roadmap'
            ], 404);
        }

        $progress->completeStep($request->step_id);
        $progress->load([
            'user:id,name,email',
            'roadmap:id,name,estimated_duration_days',
            'assignedBy:id,name'
        ]);

        return response()->json([
            'message' => 'Step completed successfully',
            'progress' => $progress
        ]);
    }

    /**
     * Uncomplete a step in the onboarding process.
     */
    public function uncompleteStep(Request $request, UserOnboardingProgress $progress): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'step_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $progress->uncompleteStep($request->step_id);
        $progress->load([
            'user:id,name,email',
            'roadmap:id,name,estimated_duration_days',
            'assignedBy:id,name'
        ]);

        return response()->json([
            'message' => 'Step uncompleted successfully',
            'progress' => $progress
        ]);
    }

    /**
     * Get current user's onboarding progress.
     */
    public function myProgress(): JsonResponse
    {
        $user = Auth::user();
        
        $progress = UserOnboardingProgress::with([
            'roadmap:id,name,description,steps,estimated_duration_days',
            'assignedBy:id,name'
        ])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($progress);
    }

    /**
     * Get detailed progress for a specific onboarding.
     */
    public function show(UserOnboardingProgress $progress): JsonResponse
    {
        $progress->load([
            'user:id,name,email',
            'roadmap:id,name,description,steps,estimated_duration_days',
            'assignedBy:id,name'
        ]);

        // Add remaining steps
        $progress->remaining_steps = $progress->remaining_steps;

        return response()->json($progress);
    }

    /**
     * Update progress notes.
     */
    public function updateNotes(Request $request, UserOnboardingProgress $progress): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $progress->update([
            'notes' => $request->notes
        ]);

        return response()->json([
            'message' => 'Notes updated successfully',
            'progress' => $progress
        ]);
    }

    /**
     * Remove onboarding assignment.
     */
    public function destroy(UserOnboardingProgress $progress): JsonResponse
    {
        $progress->delete();

        return response()->json([
            'message' => 'Onboarding assignment removed successfully'
        ]);
    }

    /**
     * Get onboarding statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_assignments' => UserOnboardingProgress::count(),
            'not_started' => UserOnboardingProgress::where('status', 'not_started')->count(),
            'in_progress' => UserOnboardingProgress::where('status', 'in_progress')->count(),
            'completed' => UserOnboardingProgress::where('status', 'completed')->count(),
            'average_completion_rate' => UserOnboardingProgress::avg('progress_percentage') ?? 0,
            'completed_this_month' => UserOnboardingProgress::where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->count()
        ];

        return response()->json($stats);
    }

    /**
     * Bulk assign roadmaps to multiple users.
     */
    public function bulkAssign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'roadmap_id' => 'required|exists:onboarding_roadmaps,id',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $assigned = [];
        $skipped = [];

        foreach ($request->user_ids as $userId) {
            // Check if user already has this roadmap assigned
            $existingProgress = UserOnboardingProgress::where('user_id', $userId)
                ->where('roadmap_id', $request->roadmap_id)
                ->first();

            if ($existingProgress) {
                $skipped[] = $userId;
                continue;
            }

            $progress = UserOnboardingProgress::create([
                'user_id' => $userId,
                'roadmap_id' => $request->roadmap_id,
                'completed_steps' => [],
                'progress_percentage' => 0,
                'status' => 'not_started',
                'notes' => $request->notes,
                'assigned_by' => Auth::id()
            ]);

            $assigned[] = $progress->id;
        }

        return response()->json([
            'message' => 'Bulk assignment completed',
            'assigned_count' => count($assigned),
            'skipped_count' => count($skipped),
            'assigned_ids' => $assigned,
            'skipped_user_ids' => $skipped
        ]);
    }
}