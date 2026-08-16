<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DeploymentBackup extends Model
{
    protected $fillable = [
        'backup_id',
        'deployment_id',
        'environment',
        'type',
        'status',
        'path',
        'storage_disk',
        'size_bytes',
        'checksum',
        'started_at',
        'finished_at',
        'duration_ms',
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

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }
}
