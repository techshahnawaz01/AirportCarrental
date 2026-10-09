<?php

namespace App\Http\Middleware;

use App\Support\FaqRegistry;
use App\Support\Icons;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Starts every request with empty per-page render state, so nothing (e.g.
 * FAQs collected for schema, icons used for the sprite) carries over when one PHP process serves many
 * requests (Octane, queue workers, tests).
 */
class ResetRenderState
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->forgetInstance(FaqRegistry::class);
        app()->forgetInstance(Icons::class);

        return $next($request);
    }
}
