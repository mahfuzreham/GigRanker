<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class AnthropicProvider implements AiProvider
{
    public function __construct(private readonly string $apiKey, private readonly string $model, private readonly string $baseUrl, private readonly int $timeout = 30) {}
    public function name(): string { return 'anthropic'; }
    public function generate(string $systemPrompt, string $userPrompt): string
    {
        $response = Http::timeout($this->timeout)->withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
        ])->acceptJson()->post(rtrim($this->baseUrl, '/').'/messages', [
            'model' => $this->model, 'max_tokens' => 4096, 'system' => $systemPrompt,
            'messages' => [['role' => 'user', 'content' => $userPrompt]],
        ]);
        if ($response->failed()) throw new RuntimeException('Anthropic request failed with HTTP '.$response->status().'.');
        $content = $response->json('content.0.text');
        if (!is_string($content) || trim($content) === '') throw new RuntimeException('Anthropic returned an empty response.');
        return trim($content);
    }
}
