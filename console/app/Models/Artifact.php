<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Artifact extends Model
{
    protected $fillable = [
        'task_id', 'project_id', 'type', 'name', 'path', 
        'mime_type', 'size', 'metadata', 'version', 'is_latest'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getUrlAttribute(): string
    {
        // 根据存储类型返回 URL
        if (starts_with($this->path, 'http')) {
            return $this->path;
        }
        return config('app.url') . '/storage/' . $this->path;
    }

    public function scopeLatest($query)
    {
        return $query->where('is_latest', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
