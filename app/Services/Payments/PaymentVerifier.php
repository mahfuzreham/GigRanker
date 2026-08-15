<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentVerifier
{
    public function verify(Payment $payment): PaymentVerificationResult;
}
