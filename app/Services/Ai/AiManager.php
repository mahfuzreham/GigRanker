<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\AiProvider;
use App\Services\AppSettings;
use RuntimeException;

class AiManager
{
    public function __construct(private readonly AppSettings $appSettings)
    {
    }

    public function driver(?string $provider = null): AiProvider
    {
        $provider ??= $this->appSettings->get('ai_provider', (string) config('gigranker.ai.default', 'gemini'));
        $fallback = config("gigranker.ai.providers.{$provider}", []);
        $model = $this->appSettings->get($provider.'_model', (string) ($fallback['model'] ?? ''));
        $apiKey = $this->appSettings->get($provider.'_api_key', (string) ($fallback['api_key'] ?? ''));

        if (! in_array($provider, ['gemini', 'groq', 'openai'], true)) {
            throw new RuntimeException("Unsupported AI provider: {$provider}");
        }

        if ($apiKey === '') {
            throw new RuntimeException("No API key configured for {$provider}. Open Admin Settings and add the provider key.");
        }

        return match ($provider) {
            'gemini' => new GeminiProvider(
                $apiKey,
                $model !== '' ? $model : 'gemini-2.5-flash',
                (int) config('gigranker.ai.timeout', 30),
            ),
            'groq', 'openai' => new OpenAiCompatibleProvider(
                $provider,
                $apiKey,
                $model,
                (string) ($fallback['base_url'] ?? ''),
                (int) config('gigranker.ai.timeout', 30),
            ),
        };
    }
}
