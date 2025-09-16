<?php
namespace Elgaml\Permission\Commands;

use Elgaml\Permission\PermissionRegistrar;
use Illuminate\Console\Command;

class ClearPermissionsCache extends Command
{
    protected $signature = 'permission:clear-cache';
    protected $description = 'Clear permissions cache';

    public function handle(PermissionRegistrar $registrar)
    {
        $registrar->forgetCachedPermissions();
        $this->info('Permissions cache cleared!');
    }
}
