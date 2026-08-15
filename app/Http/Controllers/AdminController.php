<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'users' => User::query()->count(),
            'projects' => Project::query()->count(),
            'activeSubscriptions' => Subscription::query()->where('status', 'active')->count(),
            'pendingPayments' => Payment::query()->where('status', 'pending')->count(),
            'verifiedPayments' => Payment::query()->where('status', 'verified')->count(),
            'recentPayments' => Payment::query()->with('user')->latest()->limit(8)->get(),
        ]);
    }
}
