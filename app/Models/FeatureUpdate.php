<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureUpdate extends Model
{
    protected $fillable = ['title', 'slug', 'summary', 'access_type', 'published', 'published_at'];

    protected function casts(): array
    {
        return ['published' => 'boolean', 'published_at' => 'datetime'];
    }
}
