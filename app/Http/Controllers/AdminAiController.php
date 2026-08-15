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
        $providers = ['openai','anthropic','gemini','custom'];
        $settings = [];
        foreach ($providers as $provider) {
            $settings[$provider] = [
                'api_key' => AppSetting::getValue('ai_'.$provider.'_api_key', ''),
                'api_key_set' => AppSetting::getValue('ai_'.$provider.'_api_key', null) !== null,
                'base_url' => AppSetting::getValue('ai_'.$provider.'_base_url', ''),
                'model' => AppSetting::getValue('ai_'.$provider.'_model', ''),
            ];
        }
        return view('admin.ai', [
            'providers' => $settings,
            'primary' => AppSetting::getValue('ai_primary_provider', 'openai'),
            'fallbacks' => AppSetting::getValue('ai_fallback_providers', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'primary' => ['required','in:openai,anthropic,gemini,custom'],
            'fallbacks' => ['nullable','string','max:200'],
            'openai_api_key' => ['nullable','string','max:500'], 'openai_base_url' => ['nullable','url','max:500'], 'openai_model' => ['nullable','string','max:150'],
            'anthropic_api_key' => ['nullable','string','max:500'], 'anthropic_base_url' => ['nullable','url','max:500'], 'anthropic_model' => ['nullable','string','max:150'],
            'gemini_api_key' => ['nullable','string','max:500'], 'gemini_base_url' => ['nullable','url','max:500'], 'gemini_model' => ['nullable','string','max:150'],
            'custom_api_key' => ['nullable','string','max:500'], 'custom_base_url' => ['nullable','url','max:500'], 'custom_model' => ['nullable','string','max:150'],
        ]);
        AppSetting::putValue('ai_primary_provider', $data['primary']);
        AppSetting::putValue('ai_fallback_providers', $data['fallbacks'] ?? '');
        foreach (['openai','anthropic','gemini','custom'] as $provider) {
            foreach (['api_key','base_url','model'] as $field) {
                $key = 'ai_'.$provider.'_'.$field;
                if (!empty($data[$provider.'_'.$field])) AppSetting::putValue($key, $data[$provider.'_'.$field], $field === 'api_key');
            }
        }
        return back()->with('success', 'AI provider settings saved securely.');
    }

    public function test(Request $request, AiProviderService $ai): RedirectResponse
    {
        $provider = (string) $request->input('provider');
        abort_unless(in_array($provider, ['openai','anthropic','gemini','custom'], true), 422);
        try { $result = $ai->test($provider); return back()->with('success', ucfirst($provider).' connected: '.$result['response']); }
        catch (Throwable $e) { report($e); return back()->withErrors(['ai' => ucfirst($provider).' connection failed: '.$e->getMessage()]); }
    }
}
