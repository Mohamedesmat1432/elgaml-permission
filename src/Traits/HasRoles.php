<?php
namespace Elgaml\Permission\Traits;

use Elgaml\Permission\Models\Role;
use Elgaml\Permission\Models\Permission;
use Illuminate\Support\Collection;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function assignRole($roles)
    {
        $roles = $this->getRolesArray($roles);
        $this->roles()->syncWithoutDetaching($roles);
        $this->forgetCachedPermissions();
    }

    public function givePermissionTo($permissions)
    {
        $permissions = $this->getPermissionsArray($permissions);
        $this->permissions()->syncWithoutDetaching($permissions);
        $this->forgetCachedPermissions();
    }

    public function hasRole($roles): bool
    {
        return $this->roles()->whereIn('name', $this->getRolesArray($roles))->exists();
    }

    public function hasPermissionTo($permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists() 
            || $this->roles()->whereHas('permissions', fn($q) => $q->where('name', $permission))->exists();
    }

    private function getRolesArray($roles): array
    {
        return array_wrap(is_string($roles) ? explode('|', $roles) : $roles);
    }

    private function getPermissionsArray($permissions): array
    {
        return array_wrap(is_string($permissions) ? explode('|', $permissions) : $permissions);
    }

    private function forgetCachedPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
