<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    protected $fillable = [
        'deployment_id',
        'user_id',
        'environment',
        'version',
        'commit_sha',
        'status',
        'started_at',
        'finished_at',
        'duration_ms',
        'triggered_by',
        'source',
        'message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
