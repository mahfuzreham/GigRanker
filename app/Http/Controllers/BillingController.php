<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Billing\PlanCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(): View
    {
        return view('billing.plans', [
            'plans' => PlanCatalog::all(),
            'currentPlan' => Auth::user()->plan,
        ]);
    }

    public function select(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan' => ['required', 'string', 'in:free,starter,pro,agency'],
        ]);

        $plan = PlanCatalog::get($validated['plan']);
        if ($plan === null) {
            return back()->withErrors(['plan' => 'Invalid plan.']);
        }

        if ($validated['plan'] === 'free') {
            Auth::user()->update(['plan' => 'free']);
            return redirect()->route('billing.plans')->with('success', 'Free plan selected.');
        }

        return redirect()->route('payments.create', ['plan' => $validated['plan']]);
    }
}
