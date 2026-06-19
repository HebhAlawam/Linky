<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(
            $request->user()?->isSuspended(),
            403,
            'تم إيقاف حسابك، يرجى التواصل مع إدارة Linky.',
        );

        return $next($request);
    }
}
