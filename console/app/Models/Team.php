<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team extends Model
{
    protected $fillable = [
        'project_id', 'name', 'topology', 'roles', 'blackboard'
    ];

    protected $casts = [
        'roles' => 'array',
        'blackboard' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getRole($roleSlug): ?array
    {
        return collect($this->roles)->firstWhere('slug', $roleSlug);
    }

    public function getActiveRoles(): array
    {
        return collect($this->roles)->where('active', true)->all();
    }

    public function addMessage(string $role, string $content, array $metadata = []): void
    {
        $this->blackboard[] = [
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata,
            'timestamp' => now()->toISOString(),
        ];
        $this->save();
    }
}
