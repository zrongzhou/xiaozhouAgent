<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModelProfile extends Model
{
    protected $fillable = [
        'name', 'slug', 'provider', 'model', 'base_url',
        'api_key_encrypted', 'tier', 'capabilities', 
        'cost_per_1k_input', 'cost_per_1k_output', 'max_tokens', 'is_active'
    ];

    protected $casts = [
        'capabilities' => 'array',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(ModelRecord::class);
    }

    public function getTotalCost(): float
    {
        return $this->records()->where('success', true)
            ->sum(fn($r) => $r->cost_usd);
    }

    public function getSuccessRate(): float
    {
        $total = $this->records()->count();
        if ($total === 0) return 100.0;
        return ($this->records()->where('success', true)->count() / $total) * 100;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTier($query, string $tier)
    {
        return $query->where('tier', $tier);
    }
}
