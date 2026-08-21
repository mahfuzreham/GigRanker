<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Billing\PlanCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $email = strtolower((string) $request->user()?->email);
        abort_unless($email !== '' && in_array($email, config('gigranker.admin.emails', []), true), 403);

        return view('admin.dashboard', [
            'users' => User::query()->count(),
            'projects' => Project::query()->count(),
            'activeSubscriptions' => Subscription::query()->where('status', 'active')->count(),
            'pendingPayments' => Payment::query()->where('status', 'pending')->count(),
            'approvedPayments' => Payment::query()->whereIn('status', ['approved', 'verified'])->count(),
            'plans' => PlanCatalog::all(),
            'recentPayments' => Payment::query()->with('user:id,name,email')->latest('id')->limit(8)->get(),
        ]);
    }
}
