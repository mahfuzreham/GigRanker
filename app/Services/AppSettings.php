<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AppSetting;

final class AppSettings
{
    public function get(string $key, ?string $fallback = null): ?string
    {
        $setting = AppSetting::query()->where('key', $key)->first();
        return $setting?->decrypted_value ?? $fallback;
    }

    public function put(string $key, ?string $value, bool $secret = false): void
    {
        AppSetting::putValue($key, $value, $secret);
    }

    public function allForAdmin(): array
    {
        return [
            'ai_provider' => $this->get('ai_provider', (string) config('gigranker.ai.default', 'gemini')),
            'gemini_model' => $this->get('gemini_model', (string) config('gigranker.ai.providers.gemini.model', 'gemini-2.5-flash')),
            'groq_model' => $this->get('groq_model', (string) config('gigranker.ai.providers.groq.model', 'llama-3.3-70b-versatile')),
            'openai_model' => $this->get('openai_model', (string) config('gigranker.ai.providers.openai.model', 'gpt-5-mini')),
            'bep20_address' => $this->get('bep20_address', (string) config('gigranker.payments.bep20_address', '')),
            'bep20_network' => $this->get('bep20_network', (string) config('gigranker.payments.bep20_network', 'BSC')),
            'gemini_key_set' => $this->get('gemini_api_key') !== null,
            'groq_key_set' => $this->get('groq_api_key') !== null,
            'openai_key_set' => $this->get('openai_api_key') !== null,
        ];
    }
}
