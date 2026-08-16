<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Billing\PlanCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        return view('admin.payments.index', [
            'payments' => Payment::query()
                ->with('user:id,name,email')
                ->where('status', 'pending')
                ->latest('id')
                ->paginate(25),
        ]);
    }

    public function approve(Request $request, Payment $payment): RedirectResponse
    {
        $this->ensureAdmin($request);
        $admin = $request->user();

        DB::transaction(function () use ($payment, $admin): void {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status !== 'pending') {
                throw ValidationException::withMessages(['payment' => 'Only pending payments can be approved.']);
            }

            $plan = PlanCatalog::get((string) $lockedPayment->plan);
            if ($plan === null || $plan['price'] <= 0) {
                throw ValidationException::withMessages(['payment' => 'The payment references an invalid paid plan.']);
            }

            $user = $lockedPayment->user()->lockForUpdate()->firstOrFail();
            $now = now();

            $user->update([
                'plan' => $lockedPayment->plan,
                'ai_credits' => (int) $user->ai_credits + (int) $plan['credits'],
            ]);

            $user->subscriptions()->where('status', 'active')->update([
                'status' => 'expired',
                'ends_at' => $now,
            ]);

            $user->subscriptions()->create([
                'plan' => $lockedPayment->plan,
                'status' => 'active',
                'provider' => $lockedPayment->method,
                'provider_reference' => $lockedPayment->transaction_reference,
                'starts_at' => $now,
                'ends_at' => $now->copy()->addMonth(),
            ]);

            $user->creditTransactions()->create([
                'amount' => (int) $plan['credits'],
                'type' => 'credit',
                'reason' => 'Verified '.$lockedPayment->plan.' payment',
                'reference' => $lockedPayment->merchant_reference,
            ]);

            $lockedPayment->update([
                'status' => 'approved',
                'paid_at' => $now,
                'verified_at' => $now,
                'reviewed_at' => $now,
                'verified_by_user_id' => $admin->id,
            ]);
        });

        return back()->with('success', 'Payment approved and subscription activated.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $this->ensureAdmin($request);
        $admin = $request->user();

        DB::transaction(function () use ($payment, $admin): void {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status !== 'pending') {
                throw ValidationException::withMessages(['payment' => 'Only pending payments can be rejected.']);
            }

            $now = now();
            $lockedPayment->update([
                'status' => 'rejected',
                'reviewed_at' => $now,
                'verified_by_user_id' => $admin->id,
            ]);
        });

        return back()->with('success', 'Payment rejected.');
    }

    private function ensureAdmin(Request $request): void
    {
        $email = strtolower((string) $request->user()?->email);
        abort_unless($email !== '' && in_array($email, config('gigranker.admin.emails', []), true), 403);
    }
}
