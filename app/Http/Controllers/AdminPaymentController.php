<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\Billing\PlanCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function index(): View
    {
        return view('admin.payments', [
            'payments' => Payment::query()->with('user')->latest()->paginate(25),
        ]);
    }

    public function verify(Payment $payment): RedirectResponse
    {
        abort_if($payment->status !== 'pending', 422, 'Only pending payments can be verified.');

        $plan = PlanCatalog::get($payment->plan);
        abort_unless($plan !== null && $plan['price'] > 0, 422);

        DB::transaction(function () use ($payment, $plan): void {
            $payment->update([
                'status' => 'verified',
                'paid_at' => now(),
                'verified_at' => now(),
            ]);

            Subscription::query()->where('user_id', $payment->user_id)->where('status', 'active')->update(['status' => 'expired']);

            Subscription::create([
                'user_id' => $payment->user_id,
                'plan' => $payment->plan,
                'status' => 'active',
                'provider' => $payment->method,
                'provider_reference' => $payment->merchant_reference,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);

            $payment->user()->increment('ai_credits', (int) $plan['credits']);
        });

        return back()->with('success', 'Payment verified and subscription activated.');
    }

    public function reject(Payment $payment): RedirectResponse
    {
        abort_if($payment->status !== 'pending', 422, 'Only pending payments can be rejected.');
        $payment->update(['status' => 'rejected']);

        return back()->with('success', 'Payment rejected.');
    }
}
