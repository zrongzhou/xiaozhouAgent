<?php

namespace App\Http\Resources;

use Illuminate\HttpResources\Json\JsonResource;

class TaskArtifactResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'path' => $this->path,
            'url' => $this->url,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'version' => $this->version,
            'is_latest' => $this->is_latest,
            'created_at' => $this->created_at,
        ];
    }
}
