<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_plans_and_select_free(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)
            ->get(route('billing.plans'))
            ->assertOk()
            ->assertSee('Starter')
            ->assertSee('Agency');

        $this->actingAs($user)
            ->post(route('billing.select'), ['plan' => 'free'])
            ->assertRedirect(route('billing.plans'));

        $this->assertSame('free', $user->fresh()->plan);
    }

    public function test_paid_plan_does_not_activate_without_verified_payment(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)
            ->post(route('billing.select'), ['plan' => 'pro'])
            ->assertRedirect(route('billing.plans'))
            ->assertSessionHas('info');

        $this->assertSame('free', $user->fresh()->plan);
    }
}
