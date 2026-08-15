<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Payment;

final class PaymentVerifierManager
{
    public function for(Payment $payment): PaymentVerifier
    {
        // Provider adapters can be enabled later through configuration. Until
        // credentials and destination details are configured, never auto-approve.
        return app(DisabledPaymentVerifier::class);
    }
}
