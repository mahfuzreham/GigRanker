<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FeatureRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminFeatureRequestController extends Controller
{
    public function index(): View { return view('admin.feature-requests', ['requests'=>FeatureRequest::with('user')->latest()->paginate(25)]); }
    public function update(Request $request, FeatureRequest $featureRequest): RedirectResponse {
        $data=$request->validate(['status'=>['required','in:pending,planned,in_progress,completed,rejected'],'pricing'=>['required','in:free,paid,request'],'admin_note'=>['nullable','string','max:5000']]);
        $featureRequest->update($data + ($data['status']==='completed'?['published_at'=>now()]:[]));
        return back()->with('success','Feature request updated.');
    }
}
