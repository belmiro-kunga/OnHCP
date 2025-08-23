<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseCategory;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CourseCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminCourseCategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CourseCategory::query();

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->ordered()->get();

        return response()->json([
            'data' => CourseCategoryResource::collection($categories),
            'message' => 'Categories retrieved successfully'
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = CourseCategory::create($request->validated());

        return response()->json([
            'data' => new CourseCategoryResource($category),
            'message' => 'Category created successfully'
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(CourseCategory $category): JsonResponse
    {
        $category->load('courses');

        return response()->json([
            'data' => new CourseCategoryResource($category),
            'message' => 'Category retrieved successfully'
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, CourseCategory $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json([
            'data' => new CourseCategoryResource($category),
            'message' => 'Category updated successfully'
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(CourseCategory $category): JsonResponse
    {
        // Check if category has courses
        if ($category->courses()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated courses'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Update the sort order of categories.
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:course_categories,id',
            'categories.*.sort_index' => 'required|integer|min:0'
        ]);

        foreach ($request->categories as $categoryData) {
            CourseCategory::where('id', $categoryData['id'])
                ->update(['sort_index' => $categoryData['sort_index']]);
        }

        return response()->json([
            'message' => 'Category order updated successfully'
        ]);
    }

    /**
     * Toggle the active status of a category.
     */
    public function toggleStatus(CourseCategory $category): JsonResponse
    {
        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'data' => new CourseCategoryResource($category),
            'message' => 'Category status updated successfully'
        ]);
    }
}