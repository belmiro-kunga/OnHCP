<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_lesson_id',
        'original_filename',
        's3_key',
        'upload_id',
        'upload_type',
        'status',
        'file_size',
        'content_type',
        's3_metadata',
        'mp4_url',
        'hls_url',
        'thumbnail_url',
        'duration_seconds',
        'processing_data',
        'error_message',
        'upload_completed_at',
        'processing_completed_at',
    ];

    protected $casts = [
        's3_metadata' => 'array',
        'processing_data' => 'array',
        'upload_completed_at' => 'datetime',
        'processing_completed_at' => 'datetime',
    ];

    /**
     * Get the lesson that owns this video upload.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }

    /**
     * Scope a query to only include uploads with specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include completed uploads.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed uploads.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include uploads that are processing.
     */
    public function scopeProcessing($query)
    {
        return $query->whereIn('status', ['uploading', 'uploaded', 'processing']);
    }

    /**
     * Check if the upload is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the upload failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the upload is still processing.
     */
    public function isProcessing(): bool
    {
        return in_array($this->status, ['uploading', 'uploaded', 'processing']);
    }

    /**
     * Mark the upload as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processing_completed_at' => now(),
        ]);
    }

    /**
     * Mark the upload as failed.
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Update processing status.
     */
    public function updateStatus(string $status, array $data = []): void
    {
        $updateData = ['status' => $status];
        
        if ($status === 'uploaded') {
            $updateData['upload_completed_at'] = now();
        }
        
        if (!empty($data)) {
            $updateData = array_merge($updateData, $data);
        }
        
        $this->update($updateData);
    }

    /**
     * Get the playback URLs for this video.
     */
    public function getPlaybackUrls(): array
    {
        return [
            'mp4' => $this->mp4_url,
            'hls' => $this->hls_url,
            'thumbnail' => $this->thumbnail_url,
        ];
    }

    /**
     * Get upload progress percentage.
     */
    public function getProgressPercentage(): int
    {
        return match($this->status) {
            'uploading' => 25,
            'uploaded' => 50,
            'processing' => 75,
            'completed' => 100,
            'failed' => 0,
            default => 0,
        };
    }
}