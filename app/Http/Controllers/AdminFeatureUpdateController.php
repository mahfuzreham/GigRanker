<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FeatureUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminFeatureUpdateController extends Controller
{
    public function index(): View
    {
        return view('admin.feature-updates', ['updates' => FeatureUpdate::query()->latest()->paginate(20)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required','string','max:180'],
            'summary' => ['required','string','max:5000'],
            'access_type' => ['required','in:free,paid,request'],
            'published' => ['nullable','boolean'],
        ]);
        FeatureUpdate::create($data + ['slug' => Str::slug($data['title']).'-'.Str::random(6), 'published_at' => !empty($data['published']) ? now() : null]);
        return back()->with('success', 'Feature update published/saved.');
    }
}
