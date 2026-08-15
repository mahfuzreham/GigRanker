<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Payments\PaymentActivationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function index(): View
    {
        return view('admin.payments', [
            'payments' => Payment::query()->with('user')->latest()->paginate(25),
        ]);
    }

    public function verify(Payment $payment, PaymentActivationService $activation): RedirectResponse
    {
        abort_if($payment->status !== 'pending', 422, 'Only pending payments can be verified.');
        $activation->activate($payment);

        return back()->with('success', 'Payment verified and subscription activated.');
    }

    public function reject(Payment $payment): RedirectResponse
    {
        abort_if($payment->status !== 'pending', 422, 'Only pending payments can be rejected.');
        $payment->update(['status' => 'rejected']);

        return back()->with('success', 'Payment rejected.');
    }
}
