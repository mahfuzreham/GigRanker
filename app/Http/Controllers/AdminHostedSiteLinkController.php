<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HostedSiteLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminHostedSiteLinkController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);
        return view('admin.hosted-sites', [
            'links' => HostedSiteLink::query()->orderBy('sort_order')->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'site_link' => ['required', 'url:http,https', 'max:2048'],
            'setup_link' => ['nullable', 'url:http,https', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);
        $data['is_active'] = true;
        HostedSiteLink::create($data);
        return back()->with('success', 'Hosted site link added.');
    }

    public function update(Request $request, HostedSiteLink $hostedSiteLink): RedirectResponse
    {
        $this->ensureAdmin($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'site_link' => ['required', 'url:http,https', 'max:2048'],
            'setup_link' => ['nullable', 'url:http,https', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);
        $hostedSiteLink->update($data);
        return back()->with('success', 'Hosted site link updated.');
    }

    public function toggle(Request $request, HostedSiteLink $hostedSiteLink): RedirectResponse
    {
        $this->ensureAdmin($request);
        $hostedSiteLink->update(['is_active' => ! $hostedSiteLink->is_active]);
        return back()->with('success', $hostedSiteLink->is_active ? 'Link enabled.' : 'Link disabled.');
    }

    public function destroy(Request $request, HostedSiteLink $hostedSiteLink): RedirectResponse
    {
        $this->ensureAdmin($request);
        $hostedSiteLink->delete();
        return back()->with('success', 'Hosted site link deleted.');
    }

    private function ensureAdmin(Request $request): void
    {
        $email = strtolower((string) $request->user()?->email);
        abort_unless($email !== '' && in_array($email, config('gigranker.admin.emails', []), true), 403);
    }
}
