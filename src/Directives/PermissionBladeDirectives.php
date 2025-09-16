<?php
namespace Elgaml\Permission\Directives;

use Illuminate\Support\Facades\Blade;

class PermissionBladeDirectives
{
    public function register($blade)
    {
        $blade->directive('role', function ($role) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
        });

        $blade->directive('endrole', function () {
            return '<?php endif; ?>';
        });

        $blade->directive('can', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->hasPermissionTo({$permission})): ?>";
        });

        $blade->directive('endcan', function () {
            return '<?php endif; ?>';
        });
    }
}
