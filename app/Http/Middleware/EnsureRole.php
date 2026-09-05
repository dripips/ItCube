<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Доступ по роли: `role:teacher` пускает преподавателя и администратора,
 * `role:admin` — только администратора.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $allowed = collect($roles)
            ->map(fn (string $role): ?Role => Role::tryFrom($role))
            ->filter()
            ->contains(fn (Role $role): bool => $user->role === $role
                // Администратор делает всё, что делает преподаватель: держать
                // для него отдельный набор маршрутов не за что.
                || ($role === Role::Teacher && $user->role === Role::Admin));

        abort_unless($allowed, 403);

        return $next($request);
    }
}
