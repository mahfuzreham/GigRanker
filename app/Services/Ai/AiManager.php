<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\AiProvider;
use App\Models\AppSetting;
use RuntimeException;

class AiManager
{
    public function driver(?string $provider = null): AiProvider
    {
        $provider ??= AppSetting::getValue('ai_primary_provider', config('gigranker.ai.default', 'gemini'));
        $apiKey = (string) AppSetting::getValue('ai_'.$provider.'_api_key', '');
        $model = (string) AppSetting::getValue('ai_'.$provider.'_model', '');
        $baseUrl = (string) AppSetting::getValue('ai_'.$provider.'_base_url', '');

        // Keep deployment .env as a safe fallback for the original providers.
        if ($apiKey === '') {
            $settings = config("gigranker.ai.providers.{$provider}");
            if (is_array($settings)) {
                $apiKey = (string) ($settings['api_key'] ?? '');
                $model = $model ?: (string) ($settings['model'] ?? '');
                $baseUrl = $baseUrl ?: (string) ($settings['base_url'] ?? '');
            }
        }

        $model = $model ?: match ($provider) {
            'openrouter' => 'openrouter/free',
            'groq' => 'llama-3.3-70b-versatile',
            'openai' => 'gpt-5-mini',
            'anthropic' => 'claude-sonnet-4-5',
            'gemini' => 'gemini-2.5-flash',
            default => 'default',
        };
        $baseUrl = $baseUrl ?: match ($provider) {
            'openrouter' => 'https://openrouter.ai/api/v1',
            'groq' => 'https://api.groq.com/openai/v1',
            'openai' => 'https://api.openai.com/v1',
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta/openai',
            'anthropic' => 'https://api.anthropic.com/v1',
            default => 'https://api.openai.com/v1',
        };

        if ($apiKey === '') throw new RuntimeException("{$provider} API key is not configured.");

        if ($provider === 'anthropic') {
            return new AnthropicProvider($apiKey, $model, $baseUrl, (int) config('gigranker.ai.timeout', 30));
        }
        if (!in_array($provider, ['openai','openrouter','groq','gemini','custom'], true)) {
            throw new RuntimeException("Unsupported AI provider: {$provider}");
        }
        return new OpenAiCompatibleProvider($provider, $apiKey, $model, $baseUrl, (int) config('gigranker.ai.timeout', 30));
    }
}
