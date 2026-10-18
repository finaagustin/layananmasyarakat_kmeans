<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if ($user->role !== $role) {
            abort(403, 'Akses ditolak');
        }

        return $next($request);
    }
}