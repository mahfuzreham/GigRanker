<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'user_id','provider','model','input_tokens','output_tokens','credits',
        'estimated_cost_usd','operation','status',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer', 'output_tokens' => 'integer', 'credits' => 'integer',
            'estimated_cost_usd' => 'decimal:6',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
