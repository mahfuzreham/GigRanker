<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FeatureRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class FeatureRequestController extends Controller
{
    public function index(): View { return view('feature-requests', ['requests'=>Auth::user()->featureRequests()->latest()->get()]); }
    public function store(Request $request): RedirectResponse {
        $data=$request->validate(['title'=>['required','string','max:180'],'description'=>['required','string','max:5000']]);
        Auth::user()->featureRequests()->create($data + ['pricing'=>'request','status'=>'pending']);
        return back()->with('success','Feature request submitted. Admin will review it.');
    }
}
