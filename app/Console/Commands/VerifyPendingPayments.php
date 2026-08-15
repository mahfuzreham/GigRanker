<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\Payments\PaymentActivationService;
use App\Services\Payments\PaymentVerifierManager;
use Illuminate\Console\Command;

final class VerifyPendingPayments extends Command
{
    protected $signature = 'payments:verify-pending';

    protected $description = 'Verify pending payments using configured payment-provider adapters';

    public function handle(PaymentVerifierManager $manager, PaymentActivationService $activation): int
    {
        $payments = Payment::query()->where('status', 'pending')->latest()->limit(50)->get();
        $verified = 0;

        foreach ($payments as $payment) {
            $result = $manager->for($payment)->verify($payment);

            if ($result->verified) {
                $activation->activate($payment, $result->providerReference);
                $this->info("Activated payment #{$payment->id}: {$result->message}");
                $verified++;
            } else {
                $this->line("Skipped payment #{$payment->id}: {$result->message}");
            }
        }

        $this->info("Automatic verification completed. Activated: {$verified}.");

        return self::SUCCESS;
    }
}
