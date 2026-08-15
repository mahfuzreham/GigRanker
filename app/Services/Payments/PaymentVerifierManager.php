<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Payment;

final class PaymentVerifierManager
{
    public function for(Payment $payment): PaymentVerifier
    {
        if ($payment->method === 'bep20' && filter_var(env('BEP20_AUTO_VERIFY', false), FILTER_VALIDATE_BOOL)) {
            return app(Bep20UsdtVerifier::class);
        }

        // bKash remains manual until official merchant credentials and the
        // provider's supported callback/verification flow are configured.
        return app(DisabledPaymentVerifier::class);
    }
}
