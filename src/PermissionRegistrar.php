<?php
namespace Elgaml\Permission;

use Illuminate\Cache\CacheManager;
use Elgaml\Permission\Models\Permission;
use Elgaml\Permission\Models\Role;
use Illuminate\Support\Collection;

class PermissionRegistrar
{
    protected $cache;
    protected $cacheKey = 'elgaml.permission.permissions';

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    public function getPermissions(): Collection
    {
        return $this->cache->remember($this->cacheKey, now()->addDay(), function () {
            return Permission::with('roles')->get();
        });
    }

    public function forgetCachedPermissions()
    {
        $this->cache->forget($this->cacheKey);
    }
}
