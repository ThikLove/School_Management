<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only lets the request through when the logged-in user has one of the given roles.
 *
 * Usage in routes: ->middleware('role:admin') or ->middleware('role:admin,teacher')
 */
class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasRole(...$roles)) {
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
