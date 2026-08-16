<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\AiProvider;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProvider implements AiProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $timeout = 30,
    ) {
    }

    public function name(): string
    {
        return 'gemini';
    }

    public function generate(string $systemPrompt, string $userPrompt): string
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($this->model).':generateContent?key='.rawurlencode($this->apiKey);

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->retry(3, 250, function ($exception): bool {
                    if ($exception instanceof RequestException) {
                        return in_array($exception->response->status(), [408, 429, 500, 502, 503, 504], true);
                    }

                    return true;
                })
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $userPrompt]],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                    ],
                ]);
        } catch (RequestException $exception) {
            throw new RuntimeException('Gemini request failed after retries: HTTP '.$exception->response->status().'.', 0, $exception);
        }

        if ($response->failed()) {
            throw new RuntimeException("Gemini request failed with HTTP {$response->status()}.");
        }

        $parts = $response->json('candidates.0.content.parts', []);
        $content = collect($parts)->pluck('text')->filter()->implode("\n");

        if (trim($content) === '') {
            throw new RuntimeException('Gemini returned an empty response.');
        }

        return trim($content);
    }
}
