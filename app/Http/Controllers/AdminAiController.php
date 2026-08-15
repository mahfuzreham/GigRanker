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
    private const PROVIDERS = ['openai','anthropic','gemini','openrouter','groq','custom'];

    public function edit(): View
    {
        $settings = [];
        foreach (self::PROVIDERS as $provider) {
            $settings[$provider] = [
                'api_key_set' => AppSetting::getValue('ai_'.$provider.'_api_key', null) !== null,
                'base_url' => AppSetting::getValue('ai_'.$provider.'_base_url', ''),
                'model' => AppSetting::getValue('ai_'.$provider.'_model', ''),
                'enabled' => AppSetting::getValue('ai_'.$provider.'_enabled', '0') === '1',
            ];
        }
        return view('admin.ai', ['providers'=>$settings, 'primary'=>AppSetting::getValue('ai_primary_provider','openrouter'), 'fallbacks'=>AppSetting::getValue('ai_fallback_providers','groq,gemini')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['primary'=>['required','in:'.implode(',',self::PROVIDERS)],'fallbacks'=>['nullable','string','max:300']]);
        foreach (self::PROVIDERS as $provider) {
            $data = array_merge($data, $request->validate([
                $provider.'_api_key'=>['nullable','string','max:1000'],
                $provider.'_base_url'=>['nullable','url','max:500'],
                $provider.'_model'=>['nullable','string','max:200'],
            ]));
        }
        AppSetting::putValue('ai_primary_provider',$data['primary']);
        $fallbacks = collect(explode(',',(string)($data['fallbacks']??'')))->map(fn($v)=>trim($v))->filter(fn($v)=>in_array($v,self::PROVIDERS,true))->unique()->implode(',');
        AppSetting::putValue('ai_fallback_providers',$fallbacks);
        foreach (self::PROVIDERS as $provider) {
            AppSetting::putValue('ai_'.$provider.'_enabled',$request->boolean($provider.'_enabled')?'1':'0');
            foreach (['api_key','base_url','model'] as $field) {
                $value=$data[$provider.'_'.$field]??null;
                if ($value!==null && $value!=='') AppSetting::putValue('ai_'.$provider.'_'.$field,$value,$field==='api_key');
            }
        }
        return back()->with('success','AI provider configuration saved securely.');
    }

    public function test(Request $request, AiProviderService $ai): RedirectResponse
    {
        $provider=(string)$request->input('provider');
        abort_unless(in_array($provider,self::PROVIDERS,true),422);
        try { $result=$ai->test($provider); return back()->with('success',strtoupper($provider).' connected: '.$result['response']); }
        catch(Throwable $e) { report($e); return back()->withErrors(['ai'=>strtoupper($provider).' connection failed: '.$e->getMessage()]); }
    }
}
