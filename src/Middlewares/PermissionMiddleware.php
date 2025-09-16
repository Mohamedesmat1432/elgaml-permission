<?php
namespace Elgaml\Permission\Middlewares;

use Closure;
use Elgaml\Permission\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (!auth()->user()->hasPermissionTo($permission)) {
            throw new UnauthorizedException(403, 'User does not have the right permission.');
        }

        return $next($request);
    }
}
