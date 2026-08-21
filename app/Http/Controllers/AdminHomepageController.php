<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HomepageSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminHomepageController extends Controller
{
    private const TYPES = ['features', 'plans', 'faq', 'testimonials', 'footer_links'];

    public function index(Request $request): View
    {
        $this->authorizeAdmin($request);
        $sections = HomepageSection::query()->orderBy('type')->orderBy('sort_order')->get()->groupBy('type');
        return view('admin.homepage', ['sections' => $sections, 'types' => self::TYPES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $this->validateSection($request);
        HomepageSection::create($data);
        return back()->with('success', 'Homepage section added.');
    }

    public function update(Request $request, HomepageSection $section): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $section->update($this->validateSection($request));
        return back()->with('success', 'Homepage section updated.');
    }

    public function destroy(Request $request, HomepageSection $section): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $section->delete();
        return back()->with('success', 'Homepage section deleted.');
    }

    private function validateSection(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', 'in:features,plans,faq,testimonials,footer_links'],
            'title' => ['nullable', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'items_json' => ['nullable', 'string', 'max:30000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $items = [];
        if (!empty($data['items_json'])) {
            $items = json_decode($data['items_json'], true);
            if (!is_array($items)) {
                abort(422, 'Items must be valid JSON array/object data.');
            }
        }

        return [
            'type' => $data['type'],
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'description' => $data['description'] ?? null,
            'items' => $items,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function authorizeAdmin(Request $request): void
    {
        $email = strtolower((string) $request->user()?->email);
        abort_unless($email !== '' && in_array($email, config('gigranker.admin.emails', []), true), 403);
    }
}
