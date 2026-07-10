<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupRecord extends Model
{
    protected $fillable = [
        'type', 'target', 'path', 'size', 'status', 'error_message'
    ];

    public function getErrorMessageAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByTarget($query, string $target)
    {
        return $query->where('target', $target);
    }

    public function getHumanSizeAttribute(): string
    {
        return $this->formatSize($this->size);
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
