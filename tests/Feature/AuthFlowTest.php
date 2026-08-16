<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_normalizes_email_and_authenticates_user(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => '  TEST@Example.COM ',
            'password' => 'a-very-strong-password',
            'password_confirmation' => 'a-very-strong-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertTrue(Hash::check('a-very-strong-password', User::query()->firstOrFail()->password));
    }

    public function test_login_normalizes_email_and_regenerates_session(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $oldSession = session()->getId();

        $response = $this->post(route('login.store'), [
            'email' => 'TEST@EXAMPLE.COM',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($oldSession, session()->getId());
    }

    public function test_guest_cannot_access_dashboard_or_project_creation(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('projects.create'))->assertRedirect(route('login'));
        $this->post(route('projects.store'), [])->assertRedirect(route('login'));
    }

    public function test_logout_invalidates_session_and_removes_authentication(): void
    {
        $this->actingAs(User::factory()->create());
        $oldSession = session()->getId();

        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertNotSame($oldSession, session()->getId());
    }
}
