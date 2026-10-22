<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'public_id', 'user_id', 'api_key_id', 'idempotency_key', 'status', 'file_category', 'options',
    'original_size_bytes', 'output_size_bytes', 'processing_time_ms', 'error_code', 'error_message',
    'started_at', 'completed_at',
])]
class Optimization extends Model
{
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'original_size_bytes' => 'integer',
            'output_size_bytes' => 'integer',
            'processing_time_ms' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(StoredFile::class);
    }

    public function usageRecord(): HasOne
    {
        return $this->hasOne(UsageRecord::class);
    }
}
