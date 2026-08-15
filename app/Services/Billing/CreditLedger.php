<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreditLedger
{
    public function reserve(User $user, int $amount, string $reason, string $reference): void
    {
        if ($amount < 1) {
            throw new RuntimeException('Credit amount must be positive.');
        }

        DB::transaction(function () use ($user, $amount, $reason, $reference): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->ai_credits < $amount) {
                throw new RuntimeException('Not enough AI credits.');
            }

            $lockedUser->decrement('ai_credits', $amount);

            $lockedUser->creditTransactions()->create([
                'amount' => -$amount,
                'type' => 'debit',
                'reason' => $reason,
                'reference' => $reference,
            ]);
        });
    }

    public function refund(User $user, int $amount, string $reason, string $reference): void
    {
        if ($amount < 1) {
            return;
        }

        DB::transaction(function () use ($user, $amount, $reason, $reference): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $lockedUser->increment('ai_credits', $amount);

            $lockedUser->creditTransactions()->create([
                'amount' => $amount,
                'type' => 'credit',
                'reason' => $reason,
                'reference' => $reference,
            ]);
        });
    }
}
