<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Requests\Module\StoreModuleRequest;
use App\Http\Requests\Module\UpdateModuleRequest;
use App\Http\Requests\Lesson\StoreLessonRequest;
use App\Http\Requests\Lesson\UpdateLessonRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\ModuleResource;
use App\Http\Resources\LessonResource;

class AdminCourseController extends Controller
{
    // Courses
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'created_at', 'title', 'status', 'sort_index'];
        $sortBy = $request->query('sortBy', 'created_at');
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $sortDir = strtolower($request->query('sortDir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = Course::with('modules.lessons')
            ->orderBy($sortBy, $sortDir);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        // support both 'title' and generic 'search'
        if ($title = $request->query('title')) {
            $query->where('title', 'like', "%{$title}%");
        }
        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->has('page')) {
            $perPage = (int) $request->query('per_page', 15);
            $paginated = $query->paginate($perPage);
            return CourseResource::collection($paginated)->response();
        }

        return CourseResource::collection($query->get());
    }

    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();

        $course = new Course();
        $course->title = $data['title'];
        $course->description = $data['description'] ?? null;
        $course->status = $data['status'] ?? 'draft';
        $course->sort_index = $data['sort_index'] ?? 0;

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $course->thumbnail_path = $path;
        }

        $course->save();
        return CourseResource::make($course->fresh('modules.lessons'))
            ->response()->setStatusCode(201);
    }

    public function show($id)
    {
        $course = Course::with('modules.lessons')->findOrFail($id);
        return CourseResource::make($course);
    }

    public function update(UpdateCourseRequest $request, $id)
    {
        $course = Course::findOrFail($id);
        $data = $request->validated();

        $course->fill($data);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail_path) {
                Storage::disk('public')->delete($course->thumbnail_path);
            }
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $course->thumbnail_path = $path;
        }

        $course->save();
        return CourseResource::make($course->fresh('modules.lessons'));
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        if ($course->thumbnail_path) {
            Storage::disk('public')->delete($course->thumbnail_path);
        }
        $course->delete();
        return response()->json(['message' => 'deleted']);
    }

    // Modules
    public function storeModule(StoreModuleRequest $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $data = $request->validated();

        $module = new CourseModule($data);
        $module->course_id = $course->id;
        $module->save();
        return ModuleResource::make($module->fresh('lessons'))
            ->response()->setStatusCode(201);
    }

    public function updateModule(UpdateModuleRequest $request, $courseId, $moduleId)
    {
        $module = CourseModule::where('course_id', $courseId)->findOrFail($moduleId);
        $data = $request->validated();
        $module->fill($data);
        $module->save();
        return ModuleResource::make($module->fresh('lessons'));
    }

    public function destroyModule($courseId, $moduleId)
    {
        $module = CourseModule::where('course_id', $courseId)->findOrFail($moduleId);
        $module->delete();
        return response()->json(['message' => 'deleted']);
    }

    // Lessons
    public function storeLesson(StoreLessonRequest $request, $courseId, $moduleId)
    {
        $module = CourseModule::where('course_id', $courseId)->findOrFail($moduleId);
        $data = $request->validated();

        $lesson = new CourseLesson($data);
        $lesson->course_module_id = $module->id;
        $lesson->save();
        return LessonResource::make($lesson)
            ->response()->setStatusCode(201);
    }

    public function updateLesson(UpdateLessonRequest $request, $courseId, $moduleId, $lessonId)
    {
        $module = CourseModule::where('course_id', $courseId)->findOrFail($moduleId);
        $lesson = CourseLesson::where('course_module_id', $module->id)->findOrFail($lessonId);
        $data = $request->validated();
        $lesson->fill($data);
        $lesson->save();
        return LessonResource::make($lesson);
    }

    public function destroyLesson($courseId, $moduleId, $lessonId)
    {
        $module = CourseModule::where('course_id', $courseId)->findOrFail($moduleId);
        $lesson = CourseLesson::where('course_module_id', $module->id)->findOrFail($lessonId);
        $lesson->delete();
        return response()->json(['message' => 'deleted']);
    }

    // Status transitions
    public function publish($id)
    {
        $course = Course::findOrFail($id);
        $course->status = 'published';
        $course->save();
        return CourseResource::make($course->fresh('modules.lessons'));
    }

    public function unpublish($id)
    {
        $course = Course::findOrFail($id);
        $course->status = 'draft';
        $course->save();
        return CourseResource::make($course->fresh('modules.lessons'));
    }
}
