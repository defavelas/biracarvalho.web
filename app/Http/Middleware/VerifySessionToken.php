<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySessionToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {

        if (! session()->has('token')) {
            return redirect()
                ->route('website', ['utm_source' => 'website', 'utm_medium' => 'session-expired'])
                ->with('error', 'Sessão expirada. Por favor, faça login novamente.');
        }

        return $next($request);
    }
}
