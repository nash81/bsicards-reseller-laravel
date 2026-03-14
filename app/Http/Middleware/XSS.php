<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XSS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Sanitize only normal input payload; do not touch uploaded files.
        $userInput = $request->request->all();

        array_walk_recursive($userInput, function (&$value) {
            if (is_string($value)) {
                $value = strip_tags($value);
            }
        });

        $request->request->replace($userInput);

        return $next($request);
    }
}
