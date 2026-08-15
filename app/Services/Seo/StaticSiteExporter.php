<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\Project;
use App\Models\ProjectPage;
use RuntimeException;
use ZipArchive;

class StaticSiteExporter
{
    public function export(Project $project): string
    {
        $pages = $project->pages()->where('status', 'draft')->orderBy('id')->get();
        if ($pages->isEmpty()) {
            throw new RuntimeException('Generate SEO pages before exporting the website.');
        }

        $baseUrl = rtrim((string) ($project->site_url ?: config('app.url')), '/');
        $siteName = trim((string) ($project->brand_name ?: $project->gig_title ?: 'Gig Marketing Website'));
        $homeTitle = trim((string) ($project->gig_title ?: $siteName));
        $homeDescription = trim((string) ($pages->first()->meta_description ?: 'SEO-ready marketing website for a freelance service.'));

        $files = [];
        $files['index.html'] = $this->pageHtml($project, $siteName, $homeTitle, $homeDescription, $this->homeContent($project, $pages), $pages, true, $baseUrl);

        foreach ($pages as $page) {
            $files[$page->slug.'.html'] = $this->pageHtml($project, $siteName, (string) $page->title, (string) $page->meta_description, (string) $page->content, $pages, false, $baseUrl, $page);
        }

        $files['sitemap.xml'] = $this->sitemap($pages, $baseUrl);
        $files['robots.txt'] = "User-agent: *\nAllow: /\nSitemap: {$baseUrl}/sitemap.xml\n";

        $path = tempnam(sys_get_temp_dir(), 'gigranker_');
        if ($path === false) {
            throw new RuntimeException('Unable to create export file.');
        }

        $zipPath = $path.'.zip';
        @unlink($path);
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create ZIP export.');
        }

        foreach ($files as $filename => $content) {
            $zip->addFromString($filename, $content);
        }
        $zip->close();

        return $zipPath;
    }

    private function homeContent(Project $project, $pages): string
    {
        $intro = trim((string) $project->gig_description);
        $links = $pages->take(6)->map(fn (ProjectPage $page): string => "## {$page->title}\n\nLearn more on our {$page->title} guide.")->implode("\n\n");

        return ($intro !== '' ? $intro : 'Explore practical information about this freelance service and request the service through the gig link.')
            . "\n\n## Explore Our Services\n\n".$links;
    }

    private function pageHtml(Project $project, string $siteName, string $title, string $description, string $content, $pages, bool $home, string $baseUrl, ?ProjectPage $current = null): string
    {
        $canonical = $home ? $baseUrl.'/' : $baseUrl.'/'.$current->slug.'.html';
        $cta = rtrim((string) config('app.url'), '/').'/go/'.$project->id.($current ? '?page='.$current->id : '');
        $links = $pages->filter(fn (ProjectPage $page): bool => ! $current || $page->id !== $current->id)
            ->take(8)
            ->map(fn (ProjectPage $page): string => '<li><a href="'.$this->esc($page->slug.'.html').'">'.$this->esc($page->title).'</a></li>')
            ->implode('');

        $schema = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $title,
            'description' => $description,
            'provider' => ['@type' => 'Person', 'name' => $siteName],
            'areaServed' => $project->target_country ?: 'United States',
            'url' => $canonical,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP);

        $body = $this->markdownToHtml($content);

        return '<!doctype html>\n<html lang="en">\n<head>\n<meta charset="utf-8">\n<meta name="viewport" content="width=device-width, initial-scale=1">\n<title>'.$this->esc($title).'</title>\n<meta name="description" content="'.$this->esc($description).'">\n<link rel="canonical" href="'.$this->esc($canonical).'">\n<meta property="og:title" content="'.$this->esc($title).'">\n<meta property="og:description" content="'.$this->esc($description).'">\n<script type="application/ld+json">'.$schema.'</script>\n<style>body{font-family:system-ui,Arial,sans-serif;max-width:920px;margin:auto;padding:32px;line-height:1.7;color:#172033}nav{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:30px}a{color:#0b63ce}.cta{display:inline-block;padding:12px 18px;background:#0b63ce;color:#fff;text-decoration:none;border-radius:8px;margin:18px 0}article{max-width:760px}footer{margin-top:50px;padding-top:20px;border-top:1px solid #ddd;color:#667085}</style>\n</head>\n<body>\n<nav><a href="index.html">Home</a>'.$links.'</nav>\n<main><article><h1>'.$this->esc($title).'</h1>'.$body.'<a class="cta" href="'.$this->esc($cta).'" rel="nofollow sponsored">View Service on Fiverr</a></article></main>\n<footer>'.$this->esc($siteName).' — GigRanker generated marketing website.</footer>\n</body>\n</html>\n';
    }

    private function markdownToHtml(string $content): string
    {
        $lines = preg_split('/\R/', trim($content)) ?: [];
        $html = '';
        $paragraph = [];

        $flush = function () use (&$html, &$paragraph): void {
            if ($paragraph !== []) {
                $text = trim(implode(' ', $paragraph));
                if ($text !== '') {
                    $html .= '<p>'.$this->esc($text).'</p>';
                }
                $paragraph = [];
            }
        };

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                $flush();
                continue;
            }
            if (str_starts_with($line, '## ')) {
                $flush();
                $html .= '<h2>'.$this->esc(substr($line, 3)).'</h2>';
                continue;
            }
            if (str_starts_with($line, '# ')) {
                $flush();
                $html .= '<h2>'.$this->esc(substr($line, 2)).'</h2>';
                continue;
            }
            $paragraph[] = $line;
        }
        $flush();
        return $html;
    }

    private function sitemap($pages, string $baseUrl): string
    {
        $urls = [$baseUrl.'/'];
        foreach ($pages as $page) {
            $urls[] = $baseUrl.'/'.$page->slug.'.html';
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url><loc>'.htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc></url>';
        }
        return $xml.'</urlset>';
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
