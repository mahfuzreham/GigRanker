<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\Billing\PlanCatalog;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class PaymentActivationService
{
    public function activate(Payment $payment, ?string $providerReference = null): void
    {
        $payment->refresh();
        if ($payment->status !== 'pending') {
            throw new RuntimeException('Only pending payments can be activated.');
        }

        $plan = PlanCatalog::get($payment->plan);
        if ($plan === null || $plan['price'] <= 0) {
            throw new RuntimeException('Invalid paid plan.');
        }

        DB::transaction(function () use ($payment, $providerReference): void {
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') {
                throw new RuntimeException('Payment was already processed.');
            }

            $locked->update([
                'status' => 'verified',
                'paid_at' => now(),
                'verified_at' => now(),
                'notes' => $providerReference !== null ? 'Automatically verified.' : $locked->notes,
            ]);

            Subscription::query()
                ->where('user_id', $locked->user_id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            Subscription::create([
                'user_id' => $locked->user_id,
                'plan' => $locked->plan,
                'status' => 'active',
                'provider' => $locked->method,
                'provider_reference' => $providerReference ?? $locked->merchant_reference,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);

            $locked->user()->increment('ai_credits', (int) $plan['credits']);
        });
    }
}
