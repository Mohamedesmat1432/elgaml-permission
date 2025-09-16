<?php
namespace Elgaml\Permission;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Guard;
use Elgaml\Permission\Contracts\Permission;
use Elgaml\Permission\Contracts\Role;
use Illuminate\Support\Collection;

class PermissionRegistrar
{
    protected $cache;
    protected $cacheKey = 'elgaml.permission.cache';
    protected $permissionClass;
    protected $roleClass;
    protected $teams = false;
    protected $teamId = null;

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
        $this->permissionClass = config('permission.models.permission');
        $this->roleClass = config('permission.models.role');
    }

    public function registerPermissions(): bool
    {
        app()->singleton(Permission::class, function () {
            return new $this->permissionClass();
        });

        app()->alias(Permission::class, 'permission');

        app()->singleton(Role::class, function () {
            return new $this->roleClass();
        });

        app()->alias(Role::class, 'role');

        return true;
    }

    public function getPermissions(): Collection
    {
        return $this->cache->remember($this->cacheKey, config('permission.cache.expiration_time'), function () {
            return $this->getPermissionClass()->with('roles')->get();
        });
    }

    public function getRoles(): Collection
    {
        return $this->cache->remember($this->cacheKey . '.roles', config('permission.cache.expiration_time'), function () {
            return $this->getRoleClass()->with('permissions')->get();
        });
    }

    public function forgetCachedPermissions()
    {
        $this->cache->forget($this->cacheKey);
        $this->cache->forget($this->cacheKey . '.roles');
    }

    public function getPermissionClass()
    {
        return app($this->permissionClass);
    }

    public function getRoleClass()
    {
        return app($this->roleClass);
    }

    public function setPermissionClass($permissionClass)
    {
        $this->permissionClass = $permissionClass;
        
        return $this;
    }

    public function setRoleClass($roleClass)
    {
        $this->roleClass = $roleClass;
        
        return $this;
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function setCacheKey(string $cacheKey)
    {
        $this->cacheKey = $cacheKey;
        
        return $this;
    }

    public function getTeams()
    {
        return $this->teams;
    }

    public function setTeams(bool $teams)
    {
        $this->teams = $teams;
        
        return $this;
    }

    public function getTeamId()
    {
        return $this->teamId;
    }

    public function setTeamId($teamId)
    {
        $this->teamId = $teamId;
        
        return $this;
    }

    public function getPermissionsForUser(Authorizable $user): Collection
    {
        $permissions = $user->permissions;
        
        if ($user->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles($user));
        }
        
        return $permissions->sort()->values();
    }

    protected function getPermissionsViaRoles(Authorizable $user): Collection
    {
        return $user->roles->flatMap(function ($role) {
            return $role->permissions;
        })->sort()->values();
    }
}
