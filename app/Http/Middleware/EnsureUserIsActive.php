<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out accounts that were deactivated while their session was open.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Your account has been deactivated.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 401)
                : redirect()->route('admin.login')->with('error', $message);
        }

        return $next($request);
    }
}
