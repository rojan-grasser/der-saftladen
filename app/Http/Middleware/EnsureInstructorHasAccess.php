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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $topicId = $request->route('topicId');

        $instructor = Instructor::find($request->user()->id);

        if (
            $instructor &&
            !$instructor->hasAccess(Topic::findOrFail($topicId)->professional_area_id)
        ) {
            return back()->with('error', 'Du hast keinen zugriff auf dieses Thema');
        }

        return $next($request);
    }
}
