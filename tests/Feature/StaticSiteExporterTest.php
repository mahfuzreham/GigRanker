<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectPage;
use App\Models\User;
use App\Services\Seo\StaticSiteExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class StaticSiteExporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_rejects_unsafe_page_slugs_and_escapes_generated_content(): void
    {
        $project = Project::factory()->create([
            'site_url' => 'https://example.test',
            'gig_title' => 'Safe <Title>',
            'brand_name' => 'Brand',
        ]);

        ProjectPage::query()->create([
            'project_id' => $project->id,
            'slug' => '../escape',
            'page_type' => 'service',
            'title' => 'Unsafe',
            'meta_description' => 'Unsafe',
            'content' => '<script>alert(1)</script>',
            'status' => 'draft',
        ]);

        ProjectPage::query()->create([
            'project_id' => $project->id,
            'slug' => 'safe-service',
            'page_type' => 'service',
            'title' => 'Safe <Service>',
            'meta_description' => 'Safe description',
            'content' => '<script>alert(1)</script>',
            'status' => 'draft',
        ]);

        $path = app(StaticSiteExporter::class)->export($project);
        $zip = new ZipArchive();
        $this->assertSame(true, $zip->open($path));

        $this->assertNotFalse($zip->locateName('index.html'));
        $this->assertNotFalse($zip->locateName('safe-service.html'));
        $this->assertFalse($zip->locateName('../escape.html'));
        $this->assertStringNotContainsString('<script>', (string) $zip->getFromName('safe-service.html'));
        $this->assertStringContainsString('&lt;script&gt;', (string) $zip->getFromName('safe-service.html'));
        $zip->close();
        @unlink($path);
    }

    public function test_export_rejects_non_http_site_urls_at_runtime(): void
    {
        $project = Project::factory()->create(['site_url' => 'file:///tmp/site']);
        ProjectPage::query()->create([
            'project_id' => $project->id,
            'slug' => 'safe-service',
            'page_type' => 'service',
            'title' => 'Safe Service',
            'meta_description' => 'Description',
            'content' => 'Content',
            'status' => 'draft',
        ]);

        $this->expectException(\RuntimeException::class);
        app(StaticSiteExporter::class)->export($project);
    }
}
