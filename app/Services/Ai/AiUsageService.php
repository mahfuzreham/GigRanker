<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\AiUsageLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class AiUsageService
{
    public function reserve(User $user, int $credits): void
    {
        $credits = max(1, $credits);
        DB::transaction(function () use ($user, $credits): void {
            $locked = User::query()->lockForUpdate()->findOrFail($user->id);
            if ($locked->ai_credits < $credits) {
                throw new RuntimeException('Not enough AI credits. Please upgrade your plan or add credits.');
            }
            $locked->decrement('ai_credits', $credits);
        });
    }

    public function refund(User $user, int $credits): void
    {
        if ($credits > 0) User::query()->whereKey($user->id)->increment('ai_credits', $credits);
    }

    public function record(User $user, string $provider, ?string $model, string $operation, int $credits, int $inputTokens, int $outputTokens): void
    {
        $inputRate = (float) config('gigranker.ai.pricing.'.strtolower($provider).'.input_per_million', 0);
        $outputRate = (float) config('gigranker.ai.pricing.'.strtolower($provider).'.output_per_million', 0);
        $cost = ($inputTokens / 1000000 * $inputRate) + ($outputTokens / 1000000 * $outputRate);
        AiUsageLog::create([
            'user_id' => $user->id, 'provider' => $provider, 'model' => $model,
            'input_tokens' => $inputTokens, 'output_tokens' => $outputTokens,
            'credits' => $credits, 'estimated_cost_usd' => $cost,
            'operation' => $operation, 'status' => 'success',
        ]);
    }
}
