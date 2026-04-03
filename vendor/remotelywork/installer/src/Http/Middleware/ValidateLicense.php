<?php

namespace Remotelywork\Installer\Http\Middleware;

use Closure;

class ValidateLicense
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
