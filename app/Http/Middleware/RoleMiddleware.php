<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Permission denied.');
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        return redirect()
            ->route($this->dashboardRoute($user))
            ->with('error', 'You do not have permission to access that page.');
    }

    private function dashboardRoute(User $user): string
    {
        return 'dashboard';
    }
}