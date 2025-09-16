<?php
namespace Elgaml\Permission\Traits;

use Elgaml\Permission\Models\Permission;
use Elgaml\Permission\Models\Role;
use Elgaml\Permission\Exceptions\GuardDoesNotMatch;
use Elgaml\Permission\PermissionRegistrar;
use Illuminate\Support\Collection;

trait HasRoles
{
    private $roleClass;
    private $permissionClass;

    public static function bootHasRoles()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->roles()->detach();
            $model->permissions()->detach();
        });
    }

    public function getRoleClass()
    {
        if (! isset($this->roleClass)) {
            $this->roleClass = app(PermissionRegistrar::class)->getRoleClass();
        }

        return $this->roleClass;
    }

    public function getPermissionClass()
    {
        if (! isset($this->permissionClass)) {
            $this->permissionClass = app(PermissionRegistrar::class)->getPermissionClass();
        }

        return $this->permissionClass;
    }

    public function roles()
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        );
    }

    public function permissions()
    {
        return $this->morphToMany(
            config('permission.models.permission'),
            'model',
            config('permission.table_names.model_has_permissions'),
            config('permission.column_names.model_morph_key'),
            'permission_id'
        );
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName($permission, $guardName ?? $this->getDefaultGuardName());
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById($permission, $guardName ?? $this->getDefaultGuardName());
        }

        if (! $permission instanceof $permissionClass) {
            throw new \Elgaml\Permission\Exceptions\PermissionDoesNotExist();
        }

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    public function hasDirectPermission($permission): bool
    {
        $permissionClass = $this->getPermissionClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName($permission, $this->getDefaultGuardName());
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById($permission, $this->getDefaultGuardName());
        }

        if (! $permission instanceof $permissionClass) {
            return false;
        }

        return $this->permissions->contains('id', $permission->id);
    }

    public function hasPermissionViaRole($permission): bool
    {
        return $this->hasRole($permission->roles);
    }

    public function hasAnyPermission(...$permissions): bool
    {
        $permissionClass = $this->getPermissionClass();

        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(...$permissions): bool
    {
        $permissionClass = $this->getPermissionClass();

        foreach ($permissions as $permission) {
            if (! $this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    public function hasRole($roles, string $guard = null): bool
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if (is_int($roles)) {
            return $this->roles->contains('id', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role, $guard)) {
                    return true;
                }
            }

            return false;
        }

        return $roles->intersect($this->roles)->isNotEmpty();
    }

    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole($roles);
    }

    public function hasAllRoles(...$roles): bool
    {
        $roleClass = $this->getRoleClass();

        foreach ($roles as $role) {
            if (! $this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    public function assignRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (empty($role)) {
                    return false;
                }

                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->each(function ($role) {
                $this->ensureModelSharesGuard($role);
            })
            ->map->id
            ->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->roles()->sync($roles, false);
            $this->forgetCachedPermissions();
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($roles, $model) {
                    if ($model->getKey() != $object->getKey()) {
                        return;
                    }
                    $model->roles()->sync($roles, false);
                    $model->forgetCachedPermissions();
                }
            );
        }

        return $this;
    }

    public function removeRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->map->id
            ->all();

        $this->roles()->detach($roles);
        $this->forgetCachedPermissions();

        return $this;
    }

    public function syncRoles(...$roles)
    {
        $this->roles()->detach();
        return $this->assignRole($roles);
    }

    public function givePermissionTo(...$permissions)
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                return $this->getStoredPermission($permission);
            })
            ->filter(function ($permission) {
                return $permission instanceof Permission;
            })
            ->each(function ($permission) {
                $this->ensureModelSharesGuard($permission);
            })
            ->map->id
            ->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->permissions()->sync($permissions, false);
            $this->forgetCachedPermissions();
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($permissions, $model) {
                    if ($model->getKey() != $object->getKey()) {
                        return;
                    }
                    $model->permissions()->sync($permissions, false);
                    $model->forgetCachedPermissions();
                }
            );
        }

        return $this;
    }

    public function revokePermissionTo(...$permissions)
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                return $this->getStoredPermission($permission);
            })
            ->filter(function ($permission) {
                return $permission instanceof Permission;
            })
            ->map->id
            ->all();

        $this->permissions()->detach($permissions);
        $this->forgetCachedPermissions();

        return $this;
    }

    public function syncPermissions(...$permissions)
    {
        $this->permissions()->detach();
        return $this->givePermissionTo($permissions);
    }

    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    public function getPermissionNames(): Collection
    {
        return $this->permissions->pluck('name');
    }

    public function getAllPermissions(): Collection
    {
        $permissions = $this->permissions;
        
        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }
        
        return $permissions->sort()->values();
    }

    protected function getPermissionsViaRoles(): Collection
    {
        return $this->loadMissing('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions;
            })->sort()->values();
    }

    protected function getStoredRole($role): Role
    {
        $roleClass = $this->getRoleClass();

        if (is_numeric($role)) {
            return $roleClass->findById($role, $this->getDefaultGuardName());
        }

        if (is_string($role)) {
            return $roleClass->findByName($role, $this->getDefaultGuardName());
        }

        return $role;
    }

    protected function getStoredPermission($permission): Permission
    {
        $permissionClass = $this->getPermissionClass();

        if (is_numeric($permission)) {
            return $permissionClass->findById($permission, $this->getDefaultGuardName());
        }

        if (is_string($permission)) {
            return $permissionClass->findByName($permission, $this->getDefaultGuardName());
        }

        return $permission;
    }

    protected function getDefaultGuardName(): string
    {
        return config('auth.defaults.guard');
    }

    protected function ensureModelSharesGuard($roleOrPermission)
    {
        if (! $roleOrPermission instanceof Model) {
            return;
        }

        if ($roleOrPermission->guard_name !== $this->getDefaultGuardName()) {
            throw new GuardDoesNotMatch();
        }
    }

    protected function forgetCachedPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
