<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Task extends Model
{
    protected $fillable = [
        'project_id', 'name', 'type', 'status', 'input', 
        'output', 'assigned_role', 'assigned_user_id', 'parent_id',
        'model_profile', 'cost_tokens', 'cost_ms'
    ];

    protected $casts = [
        'output' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function artifacts(): HasMany
    {
        return $this->hasMany(Artifact::class)->orderBy('version', 'desc');
    }

    public function acceptanceReport(): HasOne
    {
        return $this->hasOne(AcceptanceReport::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
