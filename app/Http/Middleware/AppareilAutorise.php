<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AppareilAutorise
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
{
    $expectedToken = hash('sha256', env('APP_KEY') . $request->userAgent());
    $cookieToken = $request->cookie('cabinet_appareil_autorise');

    if ($cookieToken !== $expectedToken) {
        abort(403, 'Accès refusé.');
    }

    return $next($request);
}

}
