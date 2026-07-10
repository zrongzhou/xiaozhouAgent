<?php

namespace App\Http\Resources;

use Illuminate\HttpResources\Json\JsonResource;

class ModelProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'provider' => $this->provider,
            'model' => $this->model,
            'base_url' => $this->base_url,
            'tier' => $this->tier,
            'capabilities' => $this->capabilities,
            'cost_per_1k_input' => $this->cost_per_1k_input,
            'cost_per_1k_output' => $this->cost_per_1k_output,
            'max_tokens' => $this->max_tokens,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'stats' => [
                'total_cost' => $this->total_cost,
                'success_rate' => $this->success_rate,
                'total_records' => $this->records()->count(),
            ],
        ];
    }
}
