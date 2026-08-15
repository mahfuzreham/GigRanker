<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\AiProvider;
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

        $response = Http::timeout($this->timeout)
            ->acceptJson()
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
