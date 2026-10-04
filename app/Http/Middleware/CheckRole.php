<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized access.');
        }

        $userRole = auth()->user()->role;

        // Super admin always has access
        if ($userRole === 'super_admin' || $userRole === 'admin') {
            return $next($request);
        }

        // Check if user has any of the allowed roles
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'You do not have the required permissions to access this module.');
    }
}
