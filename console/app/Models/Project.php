<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasUuids;

    protected $fillable = [
        'name', 'slug', 'description', 'status', 'prompt', 
        'reference_files', 'reference_images', 'style_preset', 'config'
    ];

    protected $casts = [
        'reference_files' => 'array',
        'reference_images' => 'array',
        'config' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('created_at', 'desc');
    }

    public function artifacts(): HasMany
    {
        return $this->hasMany(Artifact::class);
    }

    public function acceptanceReports(): HasMany
    {
        return $this->hasMany(AcceptanceReport::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
