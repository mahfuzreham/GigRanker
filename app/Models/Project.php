<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'gig_url',
        'gig_title',
        'gig_description',
        'service_category',
        'target_country',
        'target_city',
        'keywords',
        'brand_name',
        'fiverr_profile_url',
        'github_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
        ];
    }

    public function pages(): HasMany
    {
        return $this->hasMany(ProjectPage::class);
    }
}
