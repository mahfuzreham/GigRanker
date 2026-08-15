<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\AppSetting;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class BkashPaymentService
{
    private function baseUrl(): string
    {
        return rtrim((string) AppSetting::getValue('bkash_base_url', 'https://tokenized.pay.bka.sh/v1.2.0-beta'), '/');
    }

    private function credentials(): array
    {
        $data = [
            'app_key' => AppSetting::getValue('bkash_app_key'),
            'app_secret' => AppSetting::getValue('bkash_app_secret'),
            'username' => AppSetting::getValue('bkash_username'),
            'password' => AppSetting::getValue('bkash_password'),
        ];
        foreach ($data as $key => $value) if (!$value) throw new RuntimeException('bKash automatic payment is not configured.');
        return $data;
    }

    private function token(): string
    {
        $c = $this->credentials();
        $response = Http::timeout(20)->acceptJson()->withHeaders([
            'username' => $c['username'],
            'password' => $c['password'],
        ])->post($this->baseUrl().'/tokenized/checkout/token/grant', [
            'app_key' => $c['app_key'],
            'app_secret' => $c['app_secret'],
        ])->throw()->json();

        if (empty($response['id_token'])) throw new RuntimeException($response['statusMessage'] ?? 'Unable to obtain bKash token.');
        return (string) $response['id_token'];
    }

    public function create(Payment $payment, string $callbackUrl): string
    {
        $c = $this->credentials();
        $response = Http::timeout(20)->acceptJson()->withHeaders([
            'authorization' => $this->token(),
            'x-app-key' => $c['app_key'],
        ])->post($this->baseUrl().'/tokenized/checkout/create', [
            'mode' => '0011',
            'payerReference' => 'GR-'.$payment->user_id,
            'callbackURL' => $callbackUrl,
            'amount' => number_format((float) $payment->amount, 2, '.', ''),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $payment->merchant_reference,
        ])->throw()->json();

        if (empty($response['paymentID']) || empty($response['bkashURL'])) throw new RuntimeException($response['statusMessage'] ?? 'Unable to create bKash payment.');

        $payment->update(['transaction_reference' => $response['paymentID'], 'notes' => 'bKash payment session created.']);
        return (string) $response['bkashURL'];
    }

    public function execute(string $paymentId): array
    {
        $c = $this->credentials();
        return Http::timeout(20)->acceptJson()->withHeaders([
            'authorization' => $this->token(),
            'x-app-key' => $c['app_key'],
        ])->post($this->baseUrl().'/tokenized/checkout/execute', ['paymentID' => $paymentId])->throw()->json();
    }

    public function query(string $paymentId): array
    {
        $c = $this->credentials();
        return Http::timeout(20)->acceptJson()->withHeaders([
            'authorization' => $this->token(),
            'x-app-key' => $c['app_key'],
        ])->post($this->baseUrl().'/tokenized/checkout/payment/status', ['paymentID' => $paymentId])->throw()->json();
    }
}
