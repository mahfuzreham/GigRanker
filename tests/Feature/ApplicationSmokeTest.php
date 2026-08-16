<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationSmokeTest extends TestCase
{
    public function test_home_page_is_reachable(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_health_endpoint_is_reachable(): void
    {
        $this->get('/up')->assertOk();
    }
}
