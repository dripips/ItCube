<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Вход по логину, а не по почте: у ученика младшей группы почты может не
     * быть вовсе, и требовать её ради входа не за что.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'username' => __('Логин или пароль не подходят'),
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        return redirect()->intended(match (true) {
            $user->isAdmin() => route('admin.dashboard'),
            $user->isTeacher() => route('teach.groups.index'),
            $user->isGuardian() => route('family.index'),
            default => route('learn.index'),
        });
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
