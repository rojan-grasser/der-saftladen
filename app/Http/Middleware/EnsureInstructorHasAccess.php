<?php

namespace App\Http\Middleware;

use App\Models\Instructor;
use App\Models\Topic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstructorHasAccess
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $areaId = $request->route('areaId');

        $instructor = Instructor::find($request->user()->id);

        if (
            $instructor &&
            !$instructor->hasAccess($areaId)
        ) {
            return back()->with('error', 'Du hast keinen zugriff auf dieses Thema');
        }

        return $next($request);
    }
}
