<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject any request whose authenticated user is not an administrator.
 *
 * This guards whole route groups so that an unprotected action cannot be added
 * by accident: authorization for the dashboard and the product API lives here
 * rather than in a policy.
 */
class EnsureUserIsAdmin
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'This area is restricted to administrators.');
        }

        return $next($request);
    }
}
