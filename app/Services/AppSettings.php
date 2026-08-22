<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AppSetting;

final class AppSettings
{
    public function get(string $key, ?string $fallback = null): ?string
    {
        $setting = AppSetting::query()->where('key', $key)->first();
        return $setting?->decrypted_value ?? $fallback;
    }

    public function put(string $key, ?string $value, bool $secret = false): void
    {
        AppSetting::putValue($key, $value, $secret);
    }

    public function allForAdmin(): array
    {
        return [
            'ai_provider' => $this->get('ai_provider', (string) config('gigranker.ai.default', 'gemini')),
            'gemini_model' => $this->get('gemini_model', (string) config('gigranker.ai.providers.gemini.model', 'gemini-2.5-flash')),
            'groq_model' => $this->get('groq_model', (string) config('gigranker.ai.providers.groq.model', 'llama-3.3-70b-versatile')),
            'openai_model' => $this->get('openai_model', (string) config('gigranker.ai.providers.openai.model', 'gpt-5-mini')),
            'bep20_address' => $this->get('bep20_address', (string) config('gigranker.payments.bep20_address', '')),
            'bep20_network' => $this->get('bep20_network', (string) config('gigranker.payments.bep20_network', 'BSC')),
            'gemini_key_set' => $this->get('gemini_api_key') !== null,
            'groq_key_set' => $this->get('groq_api_key') !== null,
            'openai_key_set' => $this->get('openai_api_key') !== null,
            'site_name' => $this->get('site_name', 'GigRanker'),
            'site_tagline' => $this->get('site_tagline', 'AI-powered growth for your freelance gig.'),
            'hero_kicker' => $this->get('hero_kicker', 'AI-powered freelance growth platform'),
            'hero_title' => $this->get('hero_title', 'Turn your gig into a search-ready website.'),
            'hero_description' => $this->get('hero_description', 'Build SEO-focused pages, content and conversion paths around your freelance services.'),
            'hero_primary_text' => $this->get('hero_primary_text', 'Start Building Free'),
            'hero_secondary_text' => $this->get('hero_secondary_text', 'View Plans'),
            'features_title' => $this->get('features_title', 'Everything you need to market one gig'),
            'features_description' => $this->get('features_description', 'Create, optimize and publish your gig marketing assets from one workspace.'),
            'how_title' => $this->get('how_title', 'How GigRanker works'),
            'how_description' => $this->get('how_description', 'Create a project, generate your content and publish your marketing website.'),
            'cta_title' => $this->get('cta_title', 'Ready to grow your gig?'),
            'cta_description' => $this->get('cta_description', 'Create your first project and turn your freelance service into a focused SEO marketing asset.'),
            'footer_text' => $this->get('footer_text', '© '.date('Y').' GigRanker. All rights reserved.'),
        ];
    }

    public function home(): array
    {
        $settings = $this->allForAdmin();
        $settings['cms'] = [
            'features' => $this->jsonSetting('homepage_features', [
                ['icon' => '✦', 'title' => 'AI content generation', 'description' => 'Create targeted SEO content using your configured AI provider and credits.'],
                ['icon' => '⌕', 'title' => 'SEO-ready websites', 'description' => 'Generate structured pages with metadata, schema and conversion paths.'],
                ['icon' => '↗', 'title' => 'Gig click tracking', 'description' => 'Measure visitors moving from your marketing pages to your freelance gig.'],
            ]),
            'plans' => $this->jsonSetting('homepage_plans', [
                ['name' => 'Free', 'price' => '0', 'currency' => 'USDT', 'features' => ['Starter AI allowance', 'Project workflow'], 'cta' => 'Get Started'],
            ]),
            'faq' => $this->jsonSetting('homepage_faq', []),
            'testimonials' => $this->jsonSetting('homepage_testimonials', []),
            'footer_links' => $this->jsonSetting('homepage_footer_links', []),
        ];

        return $settings;
    }

    private function jsonSetting(string $key, array $fallback): array
    {
        $raw = $this->get($key);
        if ($raw === null || trim($raw) === '') {
            return $fallback;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : $fallback;
    }
}
