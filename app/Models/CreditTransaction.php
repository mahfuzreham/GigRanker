<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'reason',
        'reference',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
