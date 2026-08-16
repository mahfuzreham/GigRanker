<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\AiProvider;
use App\Models\Project;
use App\Services\Ai\AiManager;
use App\Services\Seo\GigSeoGenerator;
use Mockery;
use Tests\TestCase;

class GigSeoGeneratorTest extends TestCase
{
    public function test_duplicate_ai_page_slugs_are_removed(): void
    {
        $provider = Mockery::mock(AiProvider::class);
        $provider->shouldReceive('generate')->once()->andReturn(json_encode([
            'pages' => [
                ['slug' => 'web-design', 'page_type' => 'service', 'title' => 'Web Design', 'meta_description' => 'A', 'content' => 'Content A'],
                ['slug' => 'web-design', 'page_type' => 'guide', 'title' => 'Another Web Design', 'meta_description' => 'B', 'content' => 'Content B'],
                ['slug' => 'seo-services', 'page_type' => 'service', 'title' => 'SEO Services', 'meta_description' => 'C', 'content' => 'Content C'],
            ],
        ], JSON_THROW_ON_ERROR));

        $manager = Mockery::mock(AiManager::class);
        $manager->shouldReceive('driver')->once()->andReturn($provider);

        $project = new Project([
            'gig_url' => 'https://www.fiverr.com/example/service',
            'gig_title' => 'Professional Service',
            'gig_description' => 'Description',
            'keywords' => ['service'],
        ]);

        $pages = (new GigSeoGenerator($manager))->generate($project, 10);

        $this->assertCount(2, $pages);
        $this->assertSame(['web-design', 'seo-services'], array_column($pages, 'slug'));
    }
}
