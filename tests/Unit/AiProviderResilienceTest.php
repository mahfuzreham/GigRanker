<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Ai\GeminiProvider;
use App\Services\Ai\OpenAiCompatibleProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiProviderResilienceTest extends TestCase
{
    public function test_gemini_retries_transient_server_errors(): void
    {
        Http::fakeSequence()->push(['error' => 'temporary'], 503)->push([
            'candidates' => [[
                'content' => ['parts' => [['text' => 'ok']]],
            ]],
        ], 200);

        $provider = new GeminiProvider('test-key', 'test-model');

        $this->assertSame('ok', $provider->generate('system', 'user'));
        Http::assertSentCount(2);
    }

    public function test_openai_compatible_provider_retries_rate_limits(): void
    {
        Http::fakeSequence()->push(['error' => 'rate limited'], 429)->push([
            'choices' => [[
                'message' => ['content' => 'ok'],
            ]],
        ], 200);

        $provider = new OpenAiCompatibleProvider('test', 'test-key', 'test-model', 'https://example.test/v1');

        $this->assertSame('ok', $provider->generate('system', 'user'));
        Http::assertSentCount(2);
    }
}
