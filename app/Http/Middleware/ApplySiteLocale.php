<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySiteLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale((string) settings('general.default_language', config('app.locale')));

        return $next($request);
    }
}
