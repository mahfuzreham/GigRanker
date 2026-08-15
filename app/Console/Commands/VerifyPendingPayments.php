<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\Payments\PaymentActivationService;
use App\Services\Payments\PaymentVerifierManager;
use Illuminate\Console\Command;
use Throwable;

final class VerifyPendingPayments extends Command
{
    protected $signature = 'payments:verify-pending {--limit=50 : Maximum number of payments to inspect}';

    protected $description = 'Verify pending payments using configured payment-provider adapters';

    public function handle(PaymentVerifierManager $manager, PaymentActivationService $activation): int
    {
        $limit = max(1, min((int) $this->option('limit'), 200));
        $payments = Payment::query()->where('status', 'pending')->oldest()->limit($limit)->get();
        $verified = 0;

        foreach ($payments as $payment) {
            try {
                $result = $manager->for($payment)->verify($payment);

                if (! $result->verified) {
                    $this->line("Skipped payment #{$payment->id}: {$result->message}");
                    continue;
                }

                $activation->activate($payment, $result->providerReference);
                $this->info("Verified payment #{$payment->id}: {$result->message}");
                $verified++;
            } catch (Throwable $e) {
                report($e);
                $this->warn("Payment #{$payment->id} was not activated: {$e->getMessage()}");
            }
        }

        $this->info("Automatic verification completed. Verified: {$verified}.");

        return self::SUCCESS;
    }
}
