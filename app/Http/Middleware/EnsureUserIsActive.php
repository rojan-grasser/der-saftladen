<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasStatus(UserStatus::ACTIVE)) {
            if ($request->routeIs('inactive')) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        }

        if (!$user || !$user->hasStatus(UserStatus::ACTIVE)) {
            if ($request->routeIs('inactive')) {
                return $next($request);
            }
            return redirect()->route('inactive');
        }
        return $next($request);
    }
}
