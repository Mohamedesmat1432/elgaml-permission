<?php
namespace Elgaml\Permission\Middlewares;

use Closure;
use Elgaml\Permission\Exceptions\UnauthorizedException;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next, $roleOrPermission, $guard = null)
    {
        $authGuard = app('auth')->guard($guard);
        
        if (!$authGuard->check()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $rolesOrPermissions = is_array($roleOrPermission) ? $roleOrPermission : explode('|', $roleOrPermission);

        $hasRole = false;
        $hasPermission = false;

        foreach ($rolesOrPermissions as $roleOrPermission) {
            if ($authGuard->user()->hasRole($roleOrPermission)) {
                $hasRole = true;
            }
            
            if ($authGuard->user()->hasPermissionTo($roleOrPermission)) {
                $hasPermission = true;
            }
        }

        if (!$hasRole && !$hasPermission) {
            throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
        }

        return $next($request);
    }
}
