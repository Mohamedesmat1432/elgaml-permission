<?php
namespace Elgaml\Permission\Directives;

use Illuminate\Support\Facades\Blade;

class PermissionBladeDirectives
{
    public function register($blade)
    {
        // Role directives
        $blade->directive('role', function ($role) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
        });

        $blade->directive('endrole', function () {
            return '<?php endif; ?>';
        });

        $blade->directive('hasrole', function ($role) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
        });

        $blade->directive('endhasrole', function () {
            return '<?php endif; ?>';
        });

        $blade->directive('hasanyrole', function ($roles) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyRole({$roles})): ?>";
        });

        $blade->directive('endhasanyrole', function () {
            return '<?php endif; ?>';
        });

        $blade->directive('hasallroles', function ($roles) {
            return "<?php if(auth()->check() && auth()->user()->hasAllRoles({$roles})): ?>";
        });

        $blade->directive('endhasallroles', function () {
            return '<?php endif; ?>';
        });

        // Permission directives
        $blade->directive('can', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->hasPermissionTo({$permission})): ?>";
        });

        $blade->directive('endcan', function () {
            return '<?php endif; ?>';
        });

        $blade->directive('canany', function ($permissions) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyPermission({$permissions})): ?>";
        });

        $blade->directive('endcanany', function () {
            return '<?php endif; ?>';
        });

        $blade->directive('canall', function ($permissions) {
            return "<?php if(auth()->check() && auth()->user()->hasAllPermissions({$permissions})): ?>";
        });

        $blade->directive('endcanall', function () {
            return '<?php endif; ?>';
        });

        // Role or permission directives
        $blade->directive('roleorpermission', function ($roleOrPermission) {
            return "<?php if(auth()->check() && (auth()->user()->hasRole({$roleOrPermission}) || auth()->user()->hasPermissionTo({$roleOrPermission}))): ?>";
        });

        $blade->directive('endroleorpermission', function () {
            return '<?php endif; ?>';
        });

        // Else directives
        $blade->directive('elsecan', function () {
            return '<?php else: ?>';
        });

        $blade->directive('elserole', function () {
            return '<?php else: ?>';
        });
    }
}
