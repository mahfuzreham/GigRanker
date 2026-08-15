<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class BinanceAccountClient
{
    public function testConnection(): array
    {
        $key = AppSetting::getValue('binance_api_key');
        $secret = AppSetting::getValue('binance_api_secret');
        if (!$key || !$secret) throw new RuntimeException('Binance API credentials are not configured.');

        $timestamp = (string) round(microtime(true) * 1000);
        $query = 'timestamp='.$timestamp.'&recvWindow=5000';
        $signature = hash_hmac('sha256', $query, $secret);

        $response = Http::timeout(10)
            ->withHeaders(['X-MBX-APIKEY' => $key])
            ->get('https://api.binance.com/api/v3/account?'.$query.'&signature='.$signature);

        if (!$response->successful()) {
            throw new RuntimeException('Binance API rejected the request: HTTP '.$response->status());
        }

        $json = $response->json();
        return [
            'can_trade' => (bool) ($json['canTrade'] ?? false),
            'can_withdraw' => (bool) ($json['canWithdraw'] ?? false),
            'can_deposit' => (bool) ($json['canDeposit'] ?? false),
            'account_type' => (string) ($json['accountType'] ?? 'unknown'),
        ];
    }
}
