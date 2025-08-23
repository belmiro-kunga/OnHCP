<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoUploadResource extends JsonResource
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
            'lesson_id' => $this->course_lesson_id,
            'original_filename' => $this->original_filename,
            's3_key' => $this->s3_key,
            'upload_id' => $this->upload_id,
            'upload_type' => $this->upload_type,
            'status' => $this->status,
            'file_size' => $this->file_size,
            'content_type' => $this->content_type,
            'duration_seconds' => $this->duration_seconds,
            'progress_percentage' => $this->getProgressPercentage(),
            'playback_urls' => $this->when(
                $this->isCompleted(),
                $this->getPlaybackUrls()
            ),
            'error_message' => $this->when(
                $this->isFailed(),
                $this->error_message
            ),
            'upload_completed_at' => $this->upload_completed_at?->toISOString(),
            'processing_completed_at' => $this->processing_completed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}