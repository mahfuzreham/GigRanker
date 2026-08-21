<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && $this->isAdmin((string) Auth::user()->email)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ]);
        $email = Str::lower(trim($credentials['email']));
        if (! $this->isAdmin($email)) {
            return back()->withErrors(['email' => 'This account is not authorized for the admin area.'])->onlyInput('email');
        }
        if (! Auth::attempt(['email' => $email, 'password' => $credentials['password']], (bool) ($credentials['remember'] ?? false))) {
            return back()->withErrors(['email' => 'The provided admin credentials are incorrect.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    private function isAdmin(string $email): bool
    {
        return $email !== '' && in_array(Str::lower(trim($email)), config('gigranker.admin.emails', []), true);
    }
}
