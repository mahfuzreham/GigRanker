<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\AI\AiProviderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class AdminAiController extends Controller
{
    public function edit(): View
    {
        $providers = ['openai','anthropic','gemini','openrouter','groq','custom'];
        $settings = [];
        foreach ($providers as $provider) {
            $settings[$provider] = [
                'api_key_set' => AppSetting::getValue('ai_'.$provider.'_api_key', null) !== null,
                'base_url' => AppSetting::getValue('ai_'.$provider.'_base_url', ''),
                'model' => AppSetting::getValue('ai_'.$provider.'_model', ''),
            ];
        }
        return view('admin.ai', [
            'providers' => $settings,
            'primary' => AppSetting::getValue('ai_primary_provider', 'openrouter'),
            'fallbacks' => AppSetting::getValue('ai_fallback_providers', 'groq,gemini'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $providers = ['openai','anthropic','gemini','openrouter','groq','custom'];
        $data = $request->validate([
            'primary' => ['required','in:'.implode(',', $providers)],
            'fallbacks' => ['nullable','string','max:300'],
        ]);
        foreach ($providers as $provider) {
            $data = array_merge($data, $request->validate([
                $provider.'_api_key' => ['nullable','string','max:500'],
                $provider.'_base_url' => ['nullable','url','max:500'],
                $provider.'_model' => ['nullable','string','max:150'],
            ]));
        }
        AppSetting::putValue('ai_primary_provider', $data['primary']);
        AppSetting::putValue('ai_fallback_providers', $data['fallbacks'] ?? '');
        foreach ($providers as $provider) foreach (['api_key','base_url','model'] as $field) {
            $value = $data[$provider.'_'.$field] ?? null;
            if ($value !== null && $value !== '') AppSetting::putValue('ai_'.$provider.'_'.$field, $value, $field === 'api_key');
        }
        return back()->with('success', 'AI provider settings saved securely.');
    }

    public function test(Request $request, AiProviderService $ai): RedirectResponse
    {
        $provider = (string) $request->input('provider');
        abort_unless(in_array($provider, ['openai','anthropic','gemini','openrouter','groq','custom'], true), 422);
        try { $result = $ai->test($provider); return back()->with('success', ucfirst($provider).' connected: '.$result['response']); }
        catch (Throwable $e) { report($e); return back()->withErrors(['ai' => ucfirst($provider).' connection failed: '.$e->getMessage()]); }
    }
}
