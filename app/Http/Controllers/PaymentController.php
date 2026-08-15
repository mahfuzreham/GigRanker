<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Payment;
use App\Services\Billing\PlanCatalog;
use App\Services\Notifications\OrderNotificationService;
use App\Services\Payments\BkashPaymentService;
use App\Services\Payments\PaymentActivationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $plan=(string)$request->query('plan','starter'); if($plan==='free'||PlanCatalog::get($plan)===null)return redirect()->route('billing.plans');
        return view('billing.payment',['planKey'=>$plan,'plan'=>PlanCatalog::get($plan),'paymentAddress'=>config('services.payments.bep20_address'),'bkashNumber'=>config('services.payments.bkash_number'),'bkashAuto'=>(bool)AppSetting::getValue('bkash_auto_verify','0')]);
    }
    public function store(Request $request, OrderNotificationService $notifications): RedirectResponse
    {
        $v=$request->validate(['plan'=>['required','string','in:starter,pro,agency'],'method'=>['required','string','in:bkash,bep20'],'transaction_reference'=>['required','string','min:6','max:120']]);
        $plan=PlanCatalog::get($v['plan']); abort_unless($plan!==null&&$plan['price']>0,422);
        if(Payment::query()->where('method',$v['method'])->where('transaction_reference',$v['transaction_reference'])->exists())return back()->withErrors(['transaction_reference'=>'This transaction reference has already been submitted.']);
        $payment=Payment::create(['user_id'=>Auth::id(),'plan'=>$v['plan'],'method'=>$v['method'],'status'=>'pending','amount'=>$plan['price'],'currency'=>$plan['currency'],'merchant_reference'=>'GR-'.strtoupper(Str::random(20)),'transaction_reference'=>$v['transaction_reference']]);
        $notifications->paymentSubmitted($payment);
        return redirect()->route('billing.plans')->with('success','Payment submitted for verification. Your paid plan will activate only after verification.');
    }
    public function bkashStart(Request $request, BkashPaymentService $bkash, OrderNotificationService $notifications): RedirectResponse
    {
        $planKey=(string)$request->input('plan'); $plan=PlanCatalog::get($planKey); abort_unless($plan!==null&&$plan['price']>0,422);
        $payment=Payment::create(['user_id'=>Auth::id(),'plan'=>$planKey,'method'=>'bkash','status'=>'pending','amount'=>$plan['price'],'currency'=>'BDT','merchant_reference'=>'GR-'.strtoupper(Str::random(20)),'transaction_reference'=>'BKASH-PENDING']);
        $notifications->paymentSubmitted($payment);
        try{return redirect()->away($bkash->create($payment,route('payments.bkash-callback')));}catch(Throwable $e){$payment->update(['status'=>'rejected','notes'=>'bKash create failed: '.$e->getMessage()]);report($e);return back()->withErrors(['bkash'=>'Unable to start bKash payment. Please use manual payment or try again.']);}
    }
    public function bkashCallback(Request $request, BkashPaymentService $bkash, PaymentActivationService $activation): RedirectResponse
    {
        $paymentId=(string)$request->query('paymentID');$status=strtolower((string)$request->query('status'));if($paymentId==='')return redirect()->route('billing.plans')->withErrors(['bkash'=>'Missing bKash payment ID.']);
        $payment=Payment::query()->where('method','bkash')->where('transaction_reference',$paymentId)->where('status','pending')->first();if(!$payment)return redirect()->route('billing.plans')->withErrors(['bkash'=>'Payment could not be matched or was already processed.']);
        try{if(in_array($status,['failure','fail','failed','cancel','cancelled'],true))throw new \RuntimeException('bKash payment was not completed.');$result=$bkash->execute($paymentId);if(($result['transactionStatus']??'')!=='Completed')$result=$bkash->query($paymentId);if(($result['transactionStatus']??'')!=='Completed'||(string)($result['currency']??'BDT')!=='BDT'||abs((float)($result['amount']??0)-(float)$payment->amount)>0.01)throw new \RuntimeException('bKash payment could not be verified.');$activation->activate($payment,(string)($result['trxID']??$paymentId));return redirect()->route('billing.plans')->with('success','bKash payment verified and your subscription is active.');}catch(Throwable $e){report($e);return redirect()->route('billing.plans')->withErrors(['bkash'=>'Payment verification is pending or failed. Please contact support if money was deducted.']);}
    }
}
