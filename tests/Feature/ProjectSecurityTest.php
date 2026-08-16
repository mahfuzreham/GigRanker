<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_only_fiverr_gigs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('projects.store'), [
                'name' => 'Invalid project',
                'gig_url' => 'https://example.com/service',
            ])
            ->assertSessionHasErrors('gig_url');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_user_cannot_view_another_users_project_preview(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($otherUser)
            ->get(route('projects.preview', $project))
            ->assertNotFound();
    }

    public function test_user_cannot_generate_another_users_project(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($otherUser)
            ->post(route('projects.generate', $project))
            ->assertNotFound();
    }
}
