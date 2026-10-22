<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'api_key_id', 'optimization_id', 'input_bytes', 'output_bytes', 'processing_time_ms'])]
class UsageRecord extends Model
{
    protected function casts(): array
    {
        return [
            'input_bytes' => 'integer',
            'output_bytes' => 'integer',
            'processing_time_ms' => 'integer',
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

    public function optimization(): BelongsTo
    {
        return $this->belongsTo(Optimization::class);
    }
}
