<?php

namespace App\Http\Middleware;

use App\Support\FaqRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Starts every request with empty per-page render state, so nothing (e.g.
 * FAQs collected for schema) carries over when one PHP process serves many
 * requests (Octane, queue workers, tests).
 */
class ResetRenderState
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->forgetInstance(FaqRegistry::class);

        return $next($request);
    }
}
