<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_payment_review(): void
    {
        $user = User::factory()->create();
        config(['gigranker.admin.emails' => []]);

        $this->actingAs($user)
            ->get(route('admin.payments.index'))
            ->assertForbidden();
    }

    public function test_admin_can_approve_payment_once_and_activate_subscription(): void
    {
        $admin = User::factory()->create();
        $customer = User::factory()->create(['plan' => 'free', 'ai_credits' => 10]);
        config(['gigranker.admin.emails' => [strtolower($admin->email)]]);

        $payment = Payment::create([
            'user_id' => $customer->id,
            'plan' => 'pro',
            'method' => 'bkash',
            'status' => 'pending',
            'amount' => 15,
            'currency' => 'USD',
            'merchant_reference' => 'GR-APPROVE-001',
            'transaction_reference' => 'TX-APPROVE-001',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.payments.approve', $payment))
            ->assertRedirect()
            ->assertSessionHas('success');

        $customer->refresh();
        $payment->refresh();

        $this->assertSame('pro', $customer->plan);
        $this->assertSame(210, $customer->ai_credits);
        $this->assertSame('approved', $payment->status);
        $this->assertSame($admin->id, $payment->verified_by_user_id);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $customer->id,
            'plan' => 'pro',
            'status' => 'active',
            'provider' => 'bkash',
            'provider_reference' => 'TX-APPROVE-001',
        ]);
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $customer->id,
            'amount' => 200,
            'type' => 'credit',
            'reference' => 'GR-APPROVE-001',
        ]);
    }

    public function test_repeated_approval_cannot_grant_credits_twice(): void
    {
        $admin = User::factory()->create();
        $customer = User::factory()->create(['plan' => 'free', 'ai_credits' => 10]);
        config(['gigranker.admin.emails' => [strtolower($admin->email)]]);

        $payment = Payment::create([
            'user_id' => $customer->id,
            'plan' => 'starter',
            'method' => 'bep20',
            'status' => 'pending',
            'amount' => 5,
            'currency' => 'USD',
            'merchant_reference' => 'GR-IDEMPOTENT-001',
            'transaction_reference' => 'TX-IDEMPOTENT-001',
        ]);

        $this->actingAs($admin)->post(route('admin.payments.approve', $payment))->assertRedirect();
        $this->assertSame(60, $customer->fresh()->ai_credits);

        $this->actingAs($admin)
            ->post(route('admin.payments.approve', $payment))
            ->assertSessionHasErrors('payment');

        $this->assertSame(60, $customer->fresh()->ai_credits);
        $this->assertDatabaseCount('credit_transactions', 1);
        $this->assertDatabaseCount('subscriptions', 1);
    }

    public function test_admin_can_reject_pending_payment_without_activation(): void
    {
        $admin = User::factory()->create();
        $customer = User::factory()->create(['plan' => 'free', 'ai_credits' => 10]);
        config(['gigranker.admin.emails' => [strtolower($admin->email)]]);

        $payment = Payment::create([
            'user_id' => $customer->id,
            'plan' => 'agency',
            'method' => 'bep20',
            'status' => 'pending',
            'amount' => 39,
            'currency' => 'USD',
            'merchant_reference' => 'GR-REJECT-001',
            'transaction_reference' => 'TX-REJECT-001',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.payments.reject', $payment))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('rejected', $payment->fresh()->status);
        $this->assertSame('free', $customer->fresh()->plan);
        $this->assertSame(10, $customer->fresh()->ai_credits);
        $this->assertDatabaseCount('subscriptions', 0);
    }
}
