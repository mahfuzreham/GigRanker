<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\Project;
use App\Services\Ai\AiManager;
use Illuminate\Support\Str;
use RuntimeException;

class GigSeoGenerator
{
    public function __construct(private readonly AiManager $ai)
    {
    }

    /** @return array<int, array<string, string>> */
    public function generate(Project $project, int $pageCount = 10): array
    {
        $pageCount = max(1, min($pageCount, 20));

        $system = <<<'PROMPT'
You are GigRanker, an SEO content strategist. Create useful, original marketing content for a freelancer's service website. The goal is to send qualified visitors to the freelancer's gig URL. Do not promise rankings, fabricate reviews, or claim certifications that were not supplied.
Return ONLY valid JSON with a top-level "pages" array. Each page must contain: slug, page_type, title, meta_description, content. Content must be plain text with short paragraphs and markdown-style headings only. No HTML, no scripts, no keyword stuffing.
PROMPT;

        $user = json_encode([
            'project' => [
                'gig_url' => $project->gig_url,
                'gig_title' => $project->gig_title,
                'gig_description' => Str::limit((string) $project->gig_description, 6000),
                'service_category' => $project->service_category,
                'target_country' => $project->target_country,
                'target_city' => $project->target_city,
                'keywords' => $project->keywords ?? [],
                'brand_name' => $project->brand_name,
            ],
            'requirements' => [
                'page_count' => $pageCount,
                'include_homepage' => true,
                'include_services' => true,
                'include_faq' => true,
                'include_blog_style_guides' => true,
                'internal_linking_friendly_slugs' => true,
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $raw = $this->ai->driver()->generate($system, (string) $user);
        $decoded = json_decode($this->stripCodeFence($raw), true);

        if (! is_array($decoded) || ! isset($decoded['pages']) || ! is_array($decoded['pages'])) {
            throw new RuntimeException('AI returned an invalid SEO response.');
        }

        $pages = [];
        foreach ($decoded['pages'] as $page) {
            if (! is_array($page)) {
                continue;
            }

            $title = trim((string) ($page['title'] ?? ''));
            $content = trim((string) ($page['content'] ?? ''));
            if ($title === '' || $content === '') {
                continue;
            }

            $slug = Str::slug((string) ($page['slug'] ?? $title));
            if ($slug === '') {
                continue;
            }

            $pages[] = [
                'slug' => Str::limit($slug, 180, ''),
                'page_type' => Str::limit((string) ($page['page_type'] ?? 'service'), 40, ''),
                'title' => Str::limit($title, 255, ''),
                'meta_description' => Str::limit(trim((string) ($page['meta_description'] ?? '')), 320, ''),
                'content' => $content,
            ];
        }

        return array_slice($pages, 0, $pageCount);
    }

    private function stripCodeFence(string $value): string
    {
        $value = trim($value);
        if (str_starts_with($value, '```')) {
            $value = preg_replace('/^```(?:json)?\s*/i', '', $value) ?? $value;
            $value = preg_replace('/\s*```$/', '', $value) ?? $value;
        }

        return trim($value);
    }
}
