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
        $plan = PlanCatalog::get((string) $payment->plan);
        if ($plan === null || $plan['price'] <= 0) {
            throw new RuntimeException('Invalid paid plan.');
        }

        DB::transaction(function () use ($payment, $providerReference, $plan): void {
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new RuntimeException('Payment was already processed.');
            }

            if ($providerReference !== null && $providerReference !== '') {
                $duplicate = Payment::query()
                    ->whereKeyNot($locked->id)
                    ->where('method', $locked->method)
                    ->where('transaction_reference', $providerReference)
                    ->whereIn('status', ['pending', 'verified'])
                    ->exists();

                if ($duplicate) {
                    throw new RuntimeException('This transaction has already been submitted.');
                }

                $locked->transaction_reference = $providerReference;
            }

            $locked->status = 'verified';
            $locked->paid_at = now();
            $locked->verified_at = now();
            $locked->notes = $providerReference !== null ? 'Automatically verified.' : $locked->notes;
            $locked->save();

            Subscription::query()
                ->where('user_id', $locked->user_id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            Subscription::create([
                'user_id' => $locked->user_id,
                'plan' => $locked->plan,
                'status' => 'active',
                'provider' => $locked->method,
                'provider_reference' => $providerReference ?? $locked->merchant_reference ?? $locked->transaction_reference,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);

            $locked->user()->increment('ai_credits', (int) $plan['credits']);
        });
    }
}
