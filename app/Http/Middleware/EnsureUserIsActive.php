<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
        if (!$request->user()->hasStatus(UserStatus::ACTIVE)) {
            return Inertia::render('Inactive')->toResponse($request)->setStatusCode(Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
