<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\AiProvider;
use Illuminate\Http\Client\RequestException;
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

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->apiKey)
                ->acceptJson()
                ->retry(3, 250, function ($exception): bool {
                    if ($exception instanceof RequestException) {
                        return in_array($exception->response->status(), [408, 429, 500, 502, 503, 504], true);
                    }

                    return true;
                })
                ->post(rtrim($this->baseUrl, '/').'/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.4,
                ]);
        } catch (RequestException $exception) {
            throw new RuntimeException("{$this->providerName} request failed after retries: HTTP ".$exception->response->status().'.', 0, $exception);
        }

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
