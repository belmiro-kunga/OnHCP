<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_module_id',
        'title',
        'description',
        'video_url',
        'duration_seconds',
        'sort_index',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function videoUpload(): HasOne
    {
        return $this->hasOne(VideoUpload::class, 'course_lesson_id');
    }

    /**
     * Get the latest video upload for this lesson.
     */
    public function latestVideoUpload(): HasOne
    {
        return $this->hasOne(VideoUpload::class, 'course_lesson_id')->latest();
    }

    /**
     * Check if this lesson has a completed video upload.
     */
    public function hasCompletedVideo(): bool
    {
        return $this->videoUpload && $this->videoUpload->isCompleted();
    }

    /**
     * Get the video playback URLs if available.
     */
    public function getVideoPlaybackUrls(): ?array
    {
        if ($this->hasCompletedVideo()) {
            return $this->videoUpload->getPlaybackUrls();
        }
        
        return null;
    }
}
