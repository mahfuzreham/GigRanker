<?php

declare(strict_types=1);

namespace App\Services\Payments;

final readonly class PaymentVerificationResult
{
    public function __construct(
        public bool $verified,
        public string $message,
        public ?string $providerReference = null,
    ) {
    }
}
