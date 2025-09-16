<?php
namespace Elgaml\Permission\Middlewares;

use Closure;
use Elgaml\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (!auth()->user()->hasRole($role)) {
            throw new UnauthorizedException(403, 'User does not have the right role.');
        }

        return $next($request);
    }
}
