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
        ]);

        $settings->put('ai_provider', $data['ai_provider']);
        $settings->put('gemini_model', $data['gemini_model']);
        $settings->put('groq_model', $data['groq_model']);
        $settings->put('openai_model', $data['openai_model']);
        $settings->put('bep20_address', $data['bep20_address']);
        $settings->put('bep20_network', $data['bep20_network']);

        foreach (['gemini_api_key', 'groq_api_key', 'openai_api_key'] as $key) {
            if (!empty($data[$key])) {
                $settings->put($key, $data[$key], true);
            }
        }

        return back()->with('success', 'Settings saved securely. API keys are encrypted at rest.');
    }

    private function ensureAdmin(Request $request): void
    {
        $email = strtolower((string) $request->user()?->email);
        abort_unless($email !== '' && in_array($email, config('gigranker.admin.emails', []), true), 403);
    }
}
