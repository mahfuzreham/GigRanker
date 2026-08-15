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
            'bkash_app_key_set' => AppSetting::getValue('bkash_app_key', null) !== null, 'bkash_app_secret_set' => AppSetting::getValue('bkash_app_secret', null) !== null,
            'bkash_username' => AppSetting::getValue('bkash_username', ''), 'bkash_password_set' => AppSetting::getValue('bkash_password', null) !== null,
            'bkash_base_url' => AppSetting::getValue('bkash_base_url', 'https://tokenized.pay.bka.sh'),
            'bep20_payment_address' => AppSetting::getValue('bep20_payment_address', ''), 'bep20_usdt_contract' => AppSetting::getValue('bep20_usdt_contract', ''),
            'bep20_rpc_url' => AppSetting::getValue('bep20_rpc_url', ''), 'binance_api_key' => AppSetting::getValue('binance_api_key', ''),
            'binance_api_secret_set' => AppSetting::getValue('binance_api_secret', null) !== null, 'binance_enabled' => AppSetting::getValue('binance_enabled', '0'),
            'discord_webhook_set' => AppSetting::getValue('discord_order_webhook', null) !== null,
            'telegram_token_set' => AppSetting::getValue('telegram_bot_token', null) !== null,
            'telegram_order_chat_id' => AppSetting::getValue('telegram_order_chat_id', ''),
        ]]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required','string','max:100'], 'support_email' => ['nullable','email','max:190'],
            'mail_from_name' => ['required','string','max:100'], 'mail_from_address' => ['nullable','email','max:190'],
            'bkash_number' => ['nullable','string','max:30'], 'bkash_app_key' => ['nullable','string','max:255'], 'bkash_app_secret' => ['nullable','string','max:255'],
            'bkash_username' => ['nullable','string','max:190'], 'bkash_password' => ['nullable','string','max:255'], 'bkash_base_url' => ['nullable','url','max:500'],
            'bep20_payment_address' => ['nullable','string','max:100'], 'bep20_usdt_contract' => ['nullable','string','max:100'], 'bep20_rpc_url' => ['nullable','url','max:500'],
            'binance_api_key' => ['nullable','string','max:255'], 'binance_api_secret' => ['nullable','string','max:255'],
            'discord_order_webhook' => ['nullable','url','max:500'], 'telegram_bot_token' => ['nullable','string','max:255'], 'telegram_order_chat_id' => ['nullable','string','max:100'],
        ]);
        foreach (['site_name','support_email','mail_from_name','mail_from_address','bkash_number','bkash_username','bkash_base_url','bep20_payment_address','bep20_usdt_contract','bep20_rpc_url','binance_api_key','telegram_order_chat_id'] as $key) AppSetting::putValue($key, $data[$key] ?? null);
        AppSetting::putValue('bkash_auto_verify', $request->boolean('bkash_auto_verify') ? '1' : '0');
        AppSetting::putValue('binance_enabled', $request->boolean('binance_enabled') ? '1' : '0');
        foreach (['bkash_app_key','bkash_app_secret','bkash_password','binance_api_secret','discord_order_webhook','telegram_bot_token'] as $key) {
            if (!empty($data[$key])) AppSetting::putValue($key, $data[$key], in_array($key, ['bkash_app_key','bkash_app_secret','bkash_password','binance_api_secret','telegram_bot_token'], true));
        }
        return back()->with('success', 'Payment, Binance, bKash, Discord, Telegram and email settings saved securely.');
    }

    public function testBinance(): RedirectResponse
    {
        try {
            $result = app(BinanceAccountClient::class)->testConnection();
            return back()->with('success', 'Binance API connected. '.($result['can_withdraw'] ? 'WARNING: withdrawals are enabled on this API key.' : 'Withdrawals are disabled.'));
        } catch (Throwable $e) { return back()->withErrors(['binance' => 'Binance connection failed: '.$e->getMessage()]); }
    }
}
