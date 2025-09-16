<?php
namespace Elgaml\Permission\Middlewares;

use Closure;
use Elgaml\Permission\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission, $guard = null)
    {
        $authGuard = app('auth')->guard($guard);
        
        if (!$authGuard->check()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $permissions = is_array($permission) ? $permission : explode('|', $permission);

        if (!$authGuard->user()->hasAnyPermission($permissions)) {
            throw UnauthorizedException::forPermissions($permissions);
        }

        return $next($request);
    }
}
