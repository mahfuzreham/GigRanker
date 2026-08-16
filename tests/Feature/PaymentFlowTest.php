<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_plan_opens_payment_intake(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)
            ->post(route('billing.select'), ['plan' => 'pro'])
            ->assertRedirect(route('payments.create', ['plan' => 'pro']));

        $this->actingAs($user)
            ->get(route('payments.create', ['plan' => 'pro']))
            ->assertOk()
            ->assertSee('BEP20 USDT')
            ->assertSee('Transaction ID / TXID');
    }

    public function test_payment_submission_creates_pending_ledger_entry_without_activation(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)
            ->post(route('payments.store'), [
                'plan' => 'pro',
                'method' => 'bep20',
                'transaction_reference' => '0xabcdef1234567890',
            ])
            ->assertRedirect(route('billing.plans'))
            ->assertSessionHas('success');

        $payment = Payment::query()->firstOrFail();
        $this->assertSame($user->id, $payment->user_id);
        $this->assertSame('pro', $payment->plan);
        $this->assertSame('bep20', $payment->method);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('free', $user->fresh()->plan);
    }

    public function test_duplicate_payment_reference_is_rejected(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        Payment::create([
            'user_id' => $user->id,
            'plan' => 'starter',
            'method' => 'bkash',
            'status' => 'pending',
            'amount' => 5,
            'currency' => 'USD',
            'merchant_reference' => 'GR-EXISTING',
            'transaction_reference' => 'TX-123456',
        ]);

        $this->actingAs($user)
            ->from(route('payments.create', ['plan' => 'starter']))
            ->post(route('payments.store'), [
                'plan' => 'starter',
                'method' => 'bkash',
                'transaction_reference' => 'TX-123456',
            ])
            ->assertRedirect(route('payments.create', ['plan' => 'starter']))
            ->assertSessionHasErrors('transaction_reference');

        $this->assertSame(1, Payment::query()->count());
    }
}
