<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\AiProvider;
use App\Models\Project;
use App\Models\User;
use App\Services\Ai\AiManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProjectUserFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_generate_preview_export_and_track_a_click(): void
    {
        $user = User::factory()->create();
        $provider = Mockery::mock(AiProvider::class);
        $provider->shouldReceive('generate')->once()->andReturn(json_encode([
            'pages' => [
                [
                    'slug' => 'wordpress-development',
                    'page_type' => 'service',
                    'title' => 'WordPress Development',
                    'meta_description' => 'Professional WordPress development services.',
                    'content' => "## WordPress Development\n\nFast and reliable WordPress development.",
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $manager = Mockery::mock(AiManager::class);
        $manager->shouldReceive('driver')->once()->andReturn($provider);
        $this->instance(AiManager::class, $manager);

        $this->actingAs($user)
            ->post(route('projects.store'), [
                'name' => 'My Gig Website',
                'gig_url' => 'https://www.fiverr.com/example/wordpress-development',
                'site_url' => 'https://example.test',
                'gig_title' => 'Professional WordPress Development',
                'gig_description' => 'WordPress development for businesses.',
                'keywords' => "wordpress, development",
                'brand_name' => 'Example Studio',
            ])
            ->assertRedirect(route('dashboard'));

        $project = Project::query()->where('user_id', $user->id)->firstOrFail();

        $this->actingAs($user)
            ->post(route('projects.generate', $project), ['page_count' => 1])
            ->assertSessionHas('success', '1 SEO pages generated successfully.');

        $this->assertDatabaseHas('project_pages', [
            'project_id' => $project->id,
            'slug' => 'wordpress-development',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('projects.preview', $project))
            ->assertOk()
            ->assertSee('Professional WordPress Development');

        $export = $this->actingAs($user)
            ->get(route('projects.export', $project));

        $export->assertOk()
            ->assertHeader('content-type', 'application/zip');

        $page = $project->pages()->firstOrFail();
        $this->get(route('projects.click', ['project' => $project->id, 'page' => $page->id]))
            ->assertRedirect($project->gig_url);

        $this->assertDatabaseCount('project_clicks', 1);
        $this->assertDatabaseHas('project_clicks', [
            'project_id' => $project->id,
            'project_page_id' => $page->id,
        ]);
    }

    public function test_project_flow_isolated_between_users(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($otherUser)
            ->get(route('projects.preview', $project))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->get(route('projects.export', $project))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->post(route('projects.generate', $project))
            ->assertNotFound();
    }
}
