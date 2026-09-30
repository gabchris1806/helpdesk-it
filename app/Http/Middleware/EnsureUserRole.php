<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role === 'user' && $request->user()?->master_lapors_id, 403);

        return $next($request);
    }
}
