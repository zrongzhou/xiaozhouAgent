<?php

namespace App\Http\Resources;

use Illuminate\HttpResources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'input' => $this->input,
            'output' => $this->output,
            'assigned_role' => $this->assigned_role,
            'assigned_user_id' => $this->assigned_user_id,
            'parent_id' => $this->parent_id,
            'model_profile' => $this->model_profile,
            'cost_tokens' => $this->cost_tokens,
            'cost_ms' => $this->cost_ms,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'artifacts' => TaskArtifactResource::collection($this->whenLoaded('artifacts')),
            'acceptance_report' => $this->whenLoaded('acceptanceReport'),
        ];
    }
}
