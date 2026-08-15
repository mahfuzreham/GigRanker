<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\AiProvider;
use RuntimeException;

class AiManager
{
    public function driver(?string $provider = null): AiProvider
    {
        $provider ??= config('gigranker.ai.default', 'gemini');
        $settings = config("gigranker.ai.providers.{$provider}");

        if (! is_array($settings)) {
            throw new RuntimeException("Unsupported AI provider: {$provider}");
        }

        return match ($provider) {
            'gemini' => new GeminiProvider(
                (string) ($settings['api_key'] ?? ''),
                (string) ($settings['model'] ?? 'gemini-2.5-flash'),
                (int) config('gigranker.ai.timeout', 30),
            ),
            'groq', 'openai' => new OpenAiCompatibleProvider(
                $provider,
                (string) ($settings['api_key'] ?? ''),
                (string) ($settings['model'] ?? ''),
                (string) ($settings['base_url'] ?? ''),
                (int) config('gigranker.ai.timeout', 30),
            ),
            default => throw new RuntimeException("Unsupported AI provider: {$provider}"),
        };
    }
}
