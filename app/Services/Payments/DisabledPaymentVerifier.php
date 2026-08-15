<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Payment;

final class DisabledPaymentVerifier implements PaymentVerifier
{
    public function verify(Payment $payment): PaymentVerificationResult
    {
        return new PaymentVerificationResult(
            false,
            sprintf('Automatic verification is not configured for %s payments.', strtoupper($payment->method)),
        );
    }
}
