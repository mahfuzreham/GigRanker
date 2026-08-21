<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class HomepageSection extends Model
{
    protected $fillable = ['type', 'title', 'subtitle', 'description', 'items', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['items' => 'array', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }
}
