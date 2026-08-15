<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Billing\PlanCatalog;
use App\Services\Notifications\OrderNotificationService;
use App\Services\Payments\PaymentActivationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::query()->with('user')->latest();
        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search): void {
                $q->where('transaction_reference', 'like', '%'.$search.'%')
                    ->orWhere('merchant_reference', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', '%'.$search.'%'));
            });
        }
        if ($status = (string) $request->query('status')) $query->where('status', $status);
        if ($method = (string) $request->query('method')) $query->where('method', $method);

        return view('admin.payments', [
            'payments' => $query->paginate(25)->withQueryString(),
            'filters' => ['q' => $request->query('q',''), 'status' => $request->query('status',''), 'method' => $request->query('method','')],
        ]);
    }

    public function verify(Payment $payment, PaymentActivationService $activation, OrderNotificationService $notifications): RedirectResponse
    {
        abort_if($payment->status !== 'pending', 422, 'Only pending payments can be verified.');
        abort_unless(($plan = PlanCatalog::get((string) $payment->plan)) !== null && $plan['price'] > 0, 422);
        try { $activation->activate($payment); $payment->refresh(); $notifications->paymentStatusChanged($payment, 'VERIFIED'); }
        catch (Throwable $e) { report($e); return back()->withErrors(['payment' => $e->getMessage()]); }
        return back()->with('success', 'Payment verified and subscription activated.');
    }

    public function reject(Payment $payment, OrderNotificationService $notifications): RedirectResponse
    {
        abort_if($payment->status !== 'pending', 422, 'Only pending payments can be rejected.');
        $payment->update(['status' => 'rejected']);
        $payment->refresh();
        $notifications->paymentStatusChanged($payment, 'REJECTED');
        return back()->with('success', 'Payment rejected.');
    }
}
