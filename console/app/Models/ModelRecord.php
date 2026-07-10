<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelRecord extends Model
{
    protected $fillable = [
        'profile_id', 'task_type', 'tokens_input', 'tokens_output', 
        'cost_usd', 'duration_ms', 'success', 'error_message'
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ModelProfile::class);
    }

    public function getErrorMessageAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }
}
