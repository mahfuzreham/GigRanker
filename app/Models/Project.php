<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'user_id', 'name', 'gig_url', 'site_url', 'gig_title', 'gig_description',
        'service_category', 'seller_country', 'target_country', 'target_city', 'target_markets',
        'keywords', 'brand_name', 'fiverr_profile_url', 'github_url', 'status',
    ];

    protected function casts(): array
    {
        return ['keywords' => 'array', 'target_markets' => 'array'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function pages(): HasMany { return $this->hasMany(ProjectPage::class); }
    public function clicks(): HasMany { return $this->hasMany(ProjectClick::class); }
}
