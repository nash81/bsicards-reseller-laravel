<?php

namespace Remotelywork\Installer\Http\Middleware;

use Closure;

class InstallCheck
{
    public function handle($request, Closure $next)
    {
        $installlFile = storage_path('installed');

        if(!file_exists($installlFile)){
            return redirect('install');
        }

        return $next($request);
    }
}
