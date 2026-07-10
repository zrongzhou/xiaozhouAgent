<?php

namespace App\Http\Resources;

use Illuminate\HttpResources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'prompt' => $this->prompt,
            'reference_files' => $this->reference_files,
            'reference_images' => $this->reference_images,
            'style_preset' => $this->style_preset,
            'config' => $this->config,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tasks_count' => $this->tasks()->count(),
            'artifacts_count' => $this->artifacts()->count(),
            'last_activity' => $this->tasks()->latest('updated_at')->value('updated_at'),
        ];
    }
}
