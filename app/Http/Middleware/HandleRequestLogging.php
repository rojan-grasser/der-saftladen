<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleRequestLogging
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = microtime(true) - $start;

        Log::info('HTTP request', [
            'method' => $request->getMethod(),
            'uri' => $request->getPathInfo(),
            'full_url' => $request->fullUrl(),
            'query_params' => $request->query(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
            'host' => $request->getHost(),
            'accept_language' => $request->header('Accept-Language'),
            'user_id' => optional(auth()->user())->id,
            'authenticated' => auth()->check(),
            'session_id' => session()->getId(),
            'route_name' => optional($request->route())->getName(),
            'route_action' => optional($request->route())->getActionName(),
            'status_code' => $response->getStatusCode(),
            'request_duration' => $duration,
        ]);

        return $response;
    }
}
