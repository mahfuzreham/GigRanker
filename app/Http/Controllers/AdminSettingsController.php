<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AppSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminSettingsController extends Controller
{
    public function edit(Request $request, AppSettings $settings): View
    {
        $this->ensureAdmin($request);
        return view('admin.settings', ['settings' => $settings->allForAdmin()]);
    }

    public function update(Request $request, AppSettings $settings): RedirectResponse
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'ai_provider' => ['required', 'in:gemini,groq,openai'],
            'gemini_model' => ['required', 'string', 'max:120'],
            'groq_model' => ['required', 'string', 'max:120'],
            'openai_model' => ['required', 'string', 'max:120'],
            'bep20_address' => ['required', 'string', 'max:128'],
            'bep20_network' => ['required', 'in:BSC'],
            'gemini_api_key' => ['nullable', 'string', 'max:500'],
            'groq_api_key' => ['nullable', 'string', 'max:500'],
            'openai_api_key' => ['nullable', 'string', 'max:500'],
            'site_name' => ['required', 'string', 'max:100'],
            'site_tagline' => ['required', 'string', 'max:180'],
            'hero_kicker' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:220'],
            'hero_description' => ['required', 'string', 'max:500'],
            'hero_primary_text' => ['required', 'string', 'max:60'],
            'hero_secondary_text' => ['required', 'string', 'max:60'],
            'features_title' => ['required', 'string', 'max:160'],
            'features_description' => ['required', 'string', 'max:300'],
            'how_title' => ['required', 'string', 'max:160'],
            'how_description' => ['required', 'string', 'max:300'],
            'cta_title' => ['required', 'string', 'max:160'],
            'cta_description' => ['required', 'string', 'max:400'],
            'footer_text' => ['required', 'string', 'max:200'],
        ]);

        foreach (['ai_provider','gemini_model','groq_model','openai_model','bep20_address','bep20_network','site_name','site_tagline','hero_kicker','hero_title','hero_description','hero_primary_text','hero_secondary_text','features_title','features_description','how_title','how_description','cta_title','cta_description','footer_text'] as $key) {
            $settings->put($key, $data[$key]);
        }

        foreach (['gemini_api_key', 'groq_api_key', 'openai_api_key'] as $key) {
            if (!empty($data[$key])) {
                $settings->put($key, $data[$key], true);
            }
        }

        return back()->with('success', 'Settings saved. Homepage content, AI configuration and USDT settings are now live.');
    }

    private function ensureAdmin(Request $request): void
    {
        $email = strtolower((string) $request->user()?->email);
        abort_unless($email !== '' && in_array($email, config('gigranker.admin.emails', []), true), 403);
    }
}
