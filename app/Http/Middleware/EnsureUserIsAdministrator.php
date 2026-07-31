<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role === 'administrador', 403, 'Esta sección requiere permisos de administrador.');

        return $next($request);
    }
}
