<?php
namespace Elgaml\Permission\Commands;

use Elgaml\Permission\PermissionRegistrar;
use Illuminate\Console\Command;

class CachePermissions extends Command
{
    protected $signature = 'permission:cache';
    protected $description = 'Cache permissions and roles';

    public function handle(PermissionRegistrar $registrar)
    {
        $registrar->getPermissions();
        $registrar->getRoles();
        $this->info('Permissions and roles cached successfully!');
    }
}
