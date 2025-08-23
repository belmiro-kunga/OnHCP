<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /** @return array<string,mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_module_id' => $this->course_module_id,
            'title' => $this->title,
            'description' => $this->description,
            'video_url' => $this->video_url,
            'duration_seconds' => $this->duration_seconds,
            'sort_index' => $this->sort_index,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
