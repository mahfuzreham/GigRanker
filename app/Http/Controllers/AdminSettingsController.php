<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\Payments\BinanceAccountClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class AdminSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', ['settings' => [
            'site_name' => AppSetting::getValue('site_name', 'GigRanker'), 'support_email' => AppSetting::getValue('support_email', ''),
            'mail_from_name' => AppSetting::getValue('mail_from_name', 'GigRanker'), 'mail_from_address' => AppSetting::getValue('mail_from_address', ''),
            'bkash_number' => AppSetting::getValue('bkash_number', ''), 'bkash_auto_verify' => AppSetting::getValue('bkash_auto_verify', '0'),
            'bep20_payment_address' => AppSetting::getValue('bep20_payment_address', ''), 'bep20_usdt_contract' => AppSetting::getValue('bep20_usdt_contract', ''),
            'bep20_rpc_url' => AppSetting::getValue('bep20_rpc_url', ''), 'binance_api_key' => AppSetting::getValue('binance_api_key', ''),
            'binance_api_secret_set' => AppSetting::getValue('binance_api_secret', null) !== null, 'binance_enabled' => AppSetting::getValue('binance_enabled', '0'),
        ]]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required','string','max:100'], 'support_email' => ['nullable','email','max:190'],
            'mail_from_name' => ['required','string','max:100'], 'mail_from_address' => ['nullable','email','max:190'],
            'bkash_number' => ['nullable','string','max:30'], 'bep20_payment_address' => ['nullable','string','max:100'],
            'bep20_usdt_contract' => ['nullable','string','max:100'], 'bep20_rpc_url' => ['nullable','url','max:500'],
            'binance_api_key' => ['nullable','string','max:255'], 'binance_api_secret' => ['nullable','string','max:255'],
        ]);
        foreach (['site_name','support_email','mail_from_name','mail_from_address','bkash_number','bep20_payment_address','bep20_usdt_contract','bep20_rpc_url','binance_api_key'] as $key) AppSetting::putValue($key, $data[$key] ?? null);
        AppSetting::putValue('bkash_auto_verify', $request->boolean('bkash_auto_verify') ? '1' : '0');
        AppSetting::putValue('binance_enabled', $request->boolean('binance_enabled') ? '1' : '0');
        if (!empty($data['binance_api_secret'])) AppSetting::putValue('binance_api_secret', $data['binance_api_secret'], true);
        return back()->with('success', 'Payment, Binance and email settings saved securely.');
    }

    public function testBinance(): RedirectResponse
    {
        try {
            $result = app(BinanceAccountClient::class)->testConnection();
            return back()->with('success', 'Binance API connected. '.($result['can_withdraw'] ? 'WARNING: withdrawals are enabled on this API key.' : 'Withdrawals are disabled.'));
        } catch (Throwable $e) {
            return back()->withErrors(['binance' => 'Binance connection failed: '.$e->getMessage()]);
        }
    }
}
