<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Payment;
use App\Services\Billing\PlanCatalog;
use Illuminate\Support\Facades\Http;

final class Bep20UsdtVerifier implements PaymentVerifier
{
    private const TRANSFER_TOPIC = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';

    public function verify(Payment $payment): PaymentVerificationResult
    {
        if ($payment->method !== 'bep20') {
            return new PaymentVerificationResult(false, 'Payment method is not BEP20.');
        }

        $rpc = trim((string) env('BEP20_RPC_URL'));
        $destination = $this->normalizeAddress((string) env('BEP20_PAYMENT_ADDRESS'));
        $token = $this->normalizeAddress((string) env('BEP20_USDT_CONTRACT'));

        if ($rpc === '' || $destination === null || $token === null) {
            return new PaymentVerificationResult(false, 'BEP20 automatic verification is not configured.');
        }

        $txHash = strtolower(trim((string) $payment->transaction_reference));
        if (! preg_match('/^0x[a-f0-9]{64}$/', $txHash)) {
            return new PaymentVerificationResult(false, 'Invalid BEP20 transaction hash.');
        }

        try {
            $response = Http::timeout(12)->acceptJson()->post($rpc, [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'eth_getTransactionReceipt',
                'params' => [$txHash],
            ])->throw()->json();
        } catch (\Throwable) {
            return new PaymentVerificationResult(false, 'BEP20 network verification is temporarily unavailable.');
        }

        $receipt = $response['result'] ?? null;
        if (! is_array($receipt) || ($receipt['status'] ?? null) !== '0x1') {
            return new PaymentVerificationResult(false, 'Transaction is missing, failed, or not confirmed on BSC.');
        }

        $plan = PlanCatalog::get((string) $payment->plan);
        if ($plan === null || $plan['price'] <= 0 || $plan['currency'] !== 'USD') {
            return new PaymentVerificationResult(false, 'Unsupported plan for USDT verification.');
        }

        $expectedUnits = str_pad(strtolower(dechex((int) round(((float) $plan['price']) * 1_000_000))), 64, '0', STR_PAD_LEFT);

        foreach (($receipt['logs'] ?? []) as $log) {
            $topics = $log['topics'] ?? [];
            if (! is_array($topics) || count($topics) < 3) {
                continue;
            }

            $contract = $this->normalizeAddress((string) ($log['address'] ?? ''));
            $recipient = $this->normalizeAddress('0x'.substr((string) $topics[2], -40));
            $amount = strtolower(ltrim((string) ($log['data'] ?? ''), '0x'));
            $amount = str_pad($amount, 64, '0', STR_PAD_LEFT);

            if (strtolower((string) $topics[0]) === self::TRANSFER_TOPIC
                && $contract === $token
                && $recipient === $destination
                && hash_equals($expectedUnits, $amount)) {
                return new PaymentVerificationResult(true, 'BEP20 USDT transfer verified on BSC.', $txHash);
            }
        }

        return new PaymentVerificationResult(false, 'No matching USDT transfer to the configured GigRanker wallet was found.');
    }

    private function normalizeAddress(string $address): ?string
    {
        $address = strtolower(trim($address));
        return preg_match('/^0x[a-f0-9]{40}$/', $address) ? $address : null;
    }
}
