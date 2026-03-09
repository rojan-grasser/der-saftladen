<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $requireAll = true; // default behavior: require all roles

        // If last parameter is 'any', change behavior
        if (!empty($roles) && end($roles) === 'any') {
            array_pop($roles); // remove 'any' from roles
            $requireAll = false;
        }

        // Convert string roles to Role enums
        $roleEnums = array_map(fn($r) => Role::from($r), $roles);

        $user = $request->user();

        if (!$user || !$user->hasRoles($roleEnums, $requireAll)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
