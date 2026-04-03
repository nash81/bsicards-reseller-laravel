<?php

namespace Remotelywork\Installer\Http\Middleware;

use Closure;

class IsInstalled
{
    public function handle($request, Closure $next)
    {
        $installlFile = storage_path('installed');
        
        abort_if(file_exists($installlFile),404);

        return $next($request);
    }
}
