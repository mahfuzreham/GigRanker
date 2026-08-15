<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectClick extends Model
{
    protected $fillable = [
        'project_id',
        'project_page_id',
        'ip_hash',
        'referrer',
        'user_agent',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(ProjectPage::class, 'project_page_id');
    }
}
