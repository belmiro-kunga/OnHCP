<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'course' => [
                'id' => $this->course->id,
                'title' => $this->course->title,
                'description' => $this->course->description,
                'thumbnail_url' => $this->course->thumbnail_url,
                'duration_hours' => $this->course->duration_hours,
                'difficulty_level' => $this->course->difficulty_level,
                'is_active' => $this->course->is_active,
            ],
            'status' => $this->status,
            'enrolled_at' => $this->enrolled_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'progress' => [
                'percentage' => (float) $this->progress_percentage,
                'lessons_completed' => $this->lessons_completed,
                'total_lessons' => $this->total_lessons,
                'completion_rate' => $this->total_lessons > 0 
                    ? round(($this->lessons_completed / $this->total_lessons) * 100, 2)
                    : 0,
            ],
            'grade' => [
                'final_grade' => $this->final_grade ? (float) $this->final_grade : null,
                'has_grade' => $this->final_grade !== null,
            ],
            'certificate' => [
                'issued' => $this->certificate_issued,
                'issued_at' => $this->certificate_issued_at?->toISOString(),
                'eligible' => $this->isCompleted() && !$this->certificate_issued,
            ],
            'duration' => [
                'enrollment_days' => $this->enrollment_duration,
                'time_to_complete' => $this->completed_at 
                    ? $this->enrolled_at->diffInDays($this->completed_at)
                    : null,
            ],
            'lesson_progress' => $this->when(
                $this->relationLoaded('lessonProgress'),
                function () {
                    return $this->lessonProgress->map(function ($progress) {
                        return [
                            'lesson_id' => $progress->course_lesson_id,
                            'lesson_title' => $progress->lesson->title ?? null,
                            'lesson_order' => $progress->lesson->order ?? null,
                            'started' => $progress->started,
                            'completed' => $progress->completed,
                            'started_at' => $progress->started_at?->toISOString(),
                            'completed_at' => $progress->completed_at?->toISOString(),
                            'watch_time_seconds' => $progress->watch_time_seconds,
                            'total_duration_seconds' => $progress->total_duration_seconds,
                            'completion_percentage' => (float) $progress->completion_percentage,
                            'attempts' => $progress->attempts,
                            'last_accessed_at' => $progress->last_accessed_at?->toISOString(),
                            'formatted_watch_time' => $progress->formatted_watch_time,
                            'formatted_total_duration' => $progress->formatted_total_duration,
                            'remaining_time_seconds' => $progress->remaining_time,
                        ];
                    });
                }
            ),
            'course_modules' => $this->when(
                $this->relationLoaded('course') && $this->course->relationLoaded('modules'),
                function () {
                    return $this->course->modules->map(function ($module) {
                        return [
                            'id' => $module->id,
                            'title' => $module->title,
                            'description' => $module->description,
                            'order' => $module->order,
                            'lessons_count' => $module->lessons->count(),
                            'lessons' => $module->lessons->map(function ($lesson) {
                                $progress = $this->lessonProgress->firstWhere('course_lesson_id', $lesson->id);
                                return [
                                    'id' => $lesson->id,
                                    'title' => $lesson->title,
                                    'description' => $lesson->description,
                                    'order' => $lesson->order,
                                    'duration_minutes' => $lesson->duration_minutes,
                                    'video_url' => $lesson->video_url,
                                    'is_free' => $lesson->is_free,
                                    'progress' => $progress ? [
                                        'started' => $progress->started,
                                        'completed' => $progress->completed,
                                        'completion_percentage' => (float) $progress->completion_percentage,
                                        'watch_time_seconds' => $progress->watch_time_seconds,
                                        'last_accessed_at' => $progress->last_accessed_at?->toISOString(),
                                    ] : null,
                                ];
                            }),
                        ];
                    });
                }
            ),
            'metadata' => $this->enrollment_metadata,
            'timestamps' => [
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
            ],
        ];
    }
}