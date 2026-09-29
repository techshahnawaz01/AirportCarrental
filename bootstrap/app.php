<?php

use App\Http\Middleware\ApplySiteLocale;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ResetRenderState;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Catch-all CMS route is registered last so it never shadows admin or system routes.
            Route::middleware('web')->group(base_path('routes/cms.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [ResetRenderState::class], append: [SecurityHeaders::class, ApplySiteLocale::class]);
        $middleware->alias(['active' => EnsureUserIsActive::class]);
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Consistent JSON envelope for every AJAX error.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please fix the errors.',
                    'errors' => $e->errors(),
                ], $e->status);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Your session has expired. Please sign in again.'], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You are not allowed to perform this action.'], 403);
            }
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($request->expectsJson()) {
                $status = $e->getStatusCode();
                $message = match ($status) {
                    404 => 'The requested resource was not found.',
                    419 => 'Your session has expired. Please refresh the page and try again.',
                    429 => 'Too many requests. Please slow down and try again shortly.',
                    default => $e->getMessage() ?: 'Something went wrong.',
                };

                return response()->json(['success' => false, 'message' => $message], $status, $e->getHeaders());
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() && ! config('app.debug')) {
                return response()->json(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
            }
        });
    })->create();
