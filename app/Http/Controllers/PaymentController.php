<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\AppSettings;
use App\Services\Billing\PlanCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function create(Request $request, AppSettings $settings): View|RedirectResponse
    {
        $planKey = (string) $request->query('plan', 'starter');
        $plan = PlanCatalog::get($planKey);

        if ($planKey === 'free' || $plan === null || $plan['price'] <= 0) {
            return redirect()->route('billing.plans');
        }

        return view('billing.payment', [
            'planKey' => $planKey,
            'plan' => $plan,
            'paymentAddress' => $settings->get('bep20_address', config('gigranker.payments.bep20_address')),
            'bep20Network' => $settings->get('bep20_network', config('gigranker.payments.bep20_network', 'BSC')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan' => ['required', 'string', 'in:starter,pro,agency'],
            'method' => ['required', 'string', 'in:bep20'],
            'transaction_reference' => ['required', 'string', 'min:6', 'max:120'],
        ]);

        $plan = PlanCatalog::get($validated['plan']);
        abort_unless($plan !== null && $plan['price'] > 0, 422);

        if (Payment::query()->where('method', 'bep20')->where('transaction_reference', $validated['transaction_reference'])->exists()) {
            return back()->withErrors(['transaction_reference' => 'This transaction reference has already been submitted.']);
        }

        Payment::create([
            'user_id' => Auth::id(),
            'plan' => $validated['plan'],
            'method' => 'bep20',
            'status' => 'pending',
            'amount' => $plan['price'],
            'currency' => 'USDT',
            'merchant_reference' => 'GR-'.strtoupper(Str::random(20)),
            'transaction_reference' => $validated['transaction_reference'],
            'paid_at' => null,
        ]);

        return redirect()->route('billing.plans')->with('success', 'USDT payment submitted for verification. Your paid plan activates after the transaction is verified by an authorized administrator.');
    }
}
