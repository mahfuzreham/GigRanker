<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class AiProviderService
{
    public function generate(string $prompt, int $maxTokens = 1200): string
    {
        $primary = AppSetting::getValue('ai_primary_provider', 'openrouter');
        $fallbacks = array_filter(array_map('trim', explode(',', (string) AppSetting::getValue('ai_fallback_providers', ''))));
        foreach (array_unique(array_merge([$primary], $fallbacks)) as $provider) {
            try { return $this->request((string) $provider, $prompt, $maxTokens); }
            catch (\Throwable $e) { report($e); }
        }
        throw new RuntimeException('No configured AI provider is available.');
    }

    public function test(string $provider): array
    {
        $text = $this->request($provider, 'Reply with exactly: GigRanker AI connection OK', 30);
        return ['provider' => $provider, 'response' => $text];
    }

    private function request(string $provider, string $prompt, int $maxTokens): string
    {
        $prefix = 'ai_'.$provider.'_';
        $key = AppSetting::getValue($prefix.'api_key');
        $base = rtrim((string) AppSetting::getValue($prefix.'base_url', $this->defaultBase($provider)), '/');
        $model = (string) AppSetting::getValue($prefix.'model', $this->defaultModel($provider));
        if (!$key || !$model) throw new RuntimeException('AI provider is not configured: '.$provider);

        if ($provider === 'anthropic') {
            $response = Http::timeout(30)->withHeaders(['x-api-key'=>$key,'anthropic-version'=>'2023-06-01'])->post($base.'/messages', ['model'=>$model,'max_tokens'=>$maxTokens,'messages'=>[['role'=>'user','content'=>$prompt]]])->throw()->json();
            return (string) data_get($response, 'content.0.text');
        }

        $response = Http::timeout(30)->withToken($key)->post($base.'/chat/completions', ['model'=>$model,'messages'=>[['role'=>'user','content'=>$prompt]],'max_tokens'=>$maxTokens])->throw()->json();
        return (string) data_get($response, 'choices.0.message.content');
    }

    private function defaultBase(string $provider): string
    {
        return match ($provider) {
            'openai' => 'https://api.openai.com/v1',
            'anthropic' => 'https://api.anthropic.com/v1',
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta/openai',
            'openrouter' => 'https://openrouter.ai/api/v1',
            'groq' => 'https://api.groq.com/openai/v1',
            default => 'https://api.openai.com/v1',
        };
    }

    private function defaultModel(string $provider): string
    {
        return match ($provider) {
            'openai' => 'gpt-5-mini',
            'anthropic' => 'claude-sonnet-4-5',
            'gemini' => 'gemini-2.5-flash',
            'openrouter' => 'openrouter/free',
            'groq' => 'llama-3.3-70b-versatile',
            default => 'default',
        };
    }
}
