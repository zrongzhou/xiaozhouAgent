<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcceptanceReport extends Model
{
    protected $fillable = [
        'task_id', 'project_id', 'status', 
        'score_structure', 'score_layout', 'score_visual', 'score_interaction', 
        'score_total', 'details', 'human_notes'
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getPassedAttribute(): bool
    {
        return $this->status === 'passed';
    }

    public function getNeedsImprovementAttribute(): bool
    {
        return $this->status === 'failed' && $this->score_total >= 60;
    }

    public function scopePassed($query)
    {
        return $query->where('status', 'passed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
