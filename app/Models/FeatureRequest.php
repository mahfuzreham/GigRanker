<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureRequest extends Model
{
    protected $fillable = ['user_id','title','description','status','priority','pricing','admin_note','published_at'];
    protected function casts(): array { return ['published_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
