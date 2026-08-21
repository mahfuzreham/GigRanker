<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && $this->isAdmin((string) Auth::user()->email)) {
            return redirect()->route('admin.payments.index');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $credentials['email'] = Str::lower(trim($credentials['email']));

        if (! $this->isAdmin($credentials['email'])) {
            return back()->withErrors(['email' => 'This account is not authorized for admin access.'])->onlyInput('email');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The provided admin credentials are incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.payments.index'));
    }

    private function isAdmin(string $email): bool
    {
        $email = Str::lower(trim($email));

        return $email !== '' && in_array($email, config('gigranker.admin.emails', []), true);
    }
}
