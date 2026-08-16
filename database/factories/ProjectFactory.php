<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'gig_url' => 'https://www.fiverr.com/'.fake()->userName().'/professional-service',
            'site_url' => 'https://example.test',
            'gig_title' => fake()->sentence(6),
            'gig_description' => fake()->paragraph(),
            'service_category' => 'Web Development',
            'target_country' => 'United States',
            'target_city' => 'New York',
            'keywords' => ['web development', 'seo'],
            'brand_name' => 'GigRanker Demo',
            'fiverr_profile_url' => 'https://www.fiverr.com/'.fake()->userName(),
            'github_url' => 'https://github.com/example',
            'status' => 'draft',
        ];
    }
}
