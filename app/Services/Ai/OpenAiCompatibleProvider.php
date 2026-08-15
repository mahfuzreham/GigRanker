<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiCompatibleProvider implements AiProvider
{
    public function __construct(
        private readonly string $providerName,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl,
        private readonly int $timeout = 30,
    ) {
    }

    public function name(): string
    {
        return $this->providerName;
    }

    public function generate(string $systemPrompt, string $userPrompt): string
    {
        if ($this->apiKey === '') {
            throw new RuntimeException("{$this->providerName} API key is not configured.");
        }

        $response = Http::timeout($this->timeout)
            ->withToken($this->apiKey)
            ->acceptJson()
            ->post(rtrim($this->baseUrl, '/').'/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.4,
            ]);

        if ($response->failed()) {
            throw new RuntimeException("{$this->providerName} request failed with HTTP {$response->status()}.");
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException("{$this->providerName} returned an empty response.");
        }

        return trim($content);
    }
}
