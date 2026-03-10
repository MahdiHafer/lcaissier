<?php

namespace App\Http\Middleware;

use App\Support\UserPermissionCatalog;
use Closure;

class UserPermissionMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        if (!$user) {
            return $next($request);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        $requiredPermission = UserPermissionCatalog::requiredForRoute(optional($request->route())->getName());
        if (!$requiredPermission) {
            return $next($request);
        }

        if (optional($request->route())->getName() === 'settings.index') {
            if ($user->hasPermission('settings.entreprise') || $user->hasPermission('settings.exports')) {
                return $next($request);
            }
        }

        if (!$user->hasPermission($requiredPermission)) {
            abort(403, "Acces refuse pour cette fonctionnalite.");
        }

        return $next($request);
    }
}
