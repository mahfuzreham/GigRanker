<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DeploymentLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'status', 'repository', 'branch', 'commit_sha',
        'commit_message', 'details', 'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
