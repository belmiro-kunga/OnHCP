<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /** @return array<string,mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category' => new CourseCategoryResource($this->whenLoaded('category')),
            'status' => $this->status,
            'sort_index' => $this->sort_index,
            'thumbnail_path' => $this->thumbnail_path,
            'modules' => ModuleResource::collection($this->whenLoaded('modules')),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
