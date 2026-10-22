<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'optimization_id', 'role', 'disk', 'path', 'original_filename', 'mime_type', 'extension', 'size_bytes',
    'metadata', 'checksum_sha256',
])]
class StoredFile extends Model
{
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function optimization(): BelongsTo
    {
        return $this->belongsTo(Optimization::class);
    }
}
