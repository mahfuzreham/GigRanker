<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\AppSetting;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class OrderNotificationService
{
    public function paymentSubmitted(Payment $payment): void
    {
        $this->send($payment, '🛒 New GigRanker Order', 'PENDING');
    }

    public function paymentStatusChanged(Payment $payment, string $status): void
    {
        $title = $status === 'VERIFIED' ? '✅ GigRanker Payment Verified' : '❌ GigRanker Payment Rejected';
        $this->send($payment, $title, $status);
    }

    private function send(Payment $payment, string $title, string $status): void
    {
        $payment->loadMissing('user');
        $message = implode("\n", [
            $title,
            'Order: '.$payment->merchant_reference,
            'User: '.$payment->user->email,
            'Plan: '.strtoupper((string) $payment->plan),
            'Method: '.strtoupper((string) $payment->method),
            'Amount: '.$payment->currency.' '.number_format((float) $payment->amount, 2),
            'Transaction: '.$payment->transaction_reference,
            'Status: '.$status,
        ]);

        $this->discord($message);
        $this->telegram($message);
        $this->email($message, $status);
    }

    private function discord(string $message): void
    {
        $webhook = AppSetting::getValue('discord_order_webhook');
        if (!$webhook) return;
        try {
            Http::timeout(8)->post($webhook, ['content' => $message, 'allowed_mentions' => ['parse' => []]])->throw();
        } catch (\Throwable $e) {
            Log::warning('Discord order notification failed', ['error' => $e->getMessage()]);
        }
    }

    private function telegram(string $message): void
    {
        $token = AppSetting::getValue('telegram_bot_token');
        $chatId = AppSetting::getValue('telegram_order_chat_id');
        if (!$token || !$chatId) return;
        try {
            Http::timeout(8)->post('https://api.telegram.org/bot'.$token.'/sendMessage', [
                'chat_id' => $chatId, 'text' => $message, 'disable_web_page_preview' => true,
            ])->throw();
        } catch (\Throwable $e) {
            Log::warning('Telegram order notification failed', ['error' => $e->getMessage()]);
        }
    }

    private function email(string $message, string $status): void
    {
        $to = AppSetting::getValue('support_email');
        if (!$to) return;
        try {
            Mail::raw($message, function ($mail) use ($to, $status): void {
                $mail->to($to)->subject('GigRanker Payment '.$status);
            });
        } catch (\Throwable $e) {
            Log::warning('Email order notification failed', ['error' => $e->getMessage()]);
        }
    }
}
