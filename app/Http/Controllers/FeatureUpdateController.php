<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\FeatureUpdate;
use Illuminate\View\View;

final class FeatureUpdateController extends Controller
{
    public function index(): View
    {
        return view('updates.index', [
            'updates' => FeatureUpdate::query()->where('published', true)->whereNotNull('published_at')->latest('published_at')->paginate(10),
            'supportEmail' => AppSetting::getValue('support_email', 'support@gigranker.cheap'),
        ]);
    }
}
