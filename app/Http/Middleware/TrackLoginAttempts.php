<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

final class TrackLoginAttempts
{
    /**
     * Increment the login attempts for the given request.
     */
    public static function incrementAttempts(Request $request): void
    {
        $key = 'login_attempts:' . $request->ip();
        RateLimiter::hit($key, 1800); // 30 minutes
    }

    /**
     * Clear the login attempts for the given request.
     */
    public static function clearAttempts(Request $request): void
    {
        $key = 'login_attempts:' . $request->ip();
        RateLimiter::clear($key);
    }
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => __('Muitas tentativas de login. Tente novamente em :seconds segundos.', [
                    'seconds' => $seconds,
                ]),
            ], 429);
        }

        return $next($request);
    }

    /**
     * Get the throttle key for the given request.
     */
    private function throttleKey(Request $request): string
    {
        return 'login_attempts:' . $request->ip();
    }
}
