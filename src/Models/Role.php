<?php
namespace Elgaml\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Elgaml\Permission\Traits\RefreshesPermissionCache;
use Elgaml\Permission\Contracts\Role as RoleContract;
use Elgaml\Permission\Exceptions\RoleDoesNotExist;
use Elgaml\Permission\Helpers\PermissionHelper;

class Role extends Model implements RoleContract
{
    use RefreshesPermissionCache;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'level',
    ];

    protected $casts = [
        'level' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('permission.table_names.roles'));
    }

    public function permissions()
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            config('permission.table_names.role_has_permissions'),
            'role_id',
            'permission_id'
        );
    }

    public function users()
    {
        return $this->morphedByMany(
            config('auth.providers.users.model'),
            'model',
            config('permission.table_names.model_has_roles'),
            'role_id',
            config('permission.column_names.model_morph_key')
        );
    }

    public static function findByName(string $name, $guardName = null): self
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $role = static::getRoles(['name' => $name, 'guard_name' => $guardName])->first();
        
        if (!$role) {
            throw RoleDoesNotExist::create($name, $guardName);
        }

        return $role;
    }

    public static function findById(int $id, $guardName = null): self
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $role = static::getRoles(['id' => $id, 'guard_name' => $guardName])->first();

        if (!$role) {
            throw RoleDoesNotExist::withId($id, $guardName);
        }

        return $role;
    }

    public static function findOrCreate(string $name, $guardName = null, $description = null, $level = 0): self
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $role = static::getRoles(['name' => $name, 'guard_name' => $guardName])->first();

        if (!$role) {
            return static::query()->create([
                'name' => $name, 
                'guard_name' => $guardName,
                'description' => $description,
                'level' => $level,
            ]);
        }

        return $role;
    }

    public function givePermissionTo(...$permissions)
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                return $this->getStoredPermission($permission);
            })
            ->each(function ($permission) {
                $this->ensureModelSharesGuard($permission);
            })
            ->all();

        $this->permissions()->saveMany($permissions);
        $this->forgetCachedPermissions();

        return $this;
    }

    public function revokePermissionTo($permission)
    {
        $this->permissions()->detach($this->getStoredPermission($permission));
        $this->forgetCachedPermissions();

        return $this;
    }

    public function syncPermissions(...$permissions)
    {
        $this->permissions()->detach();
        return $this->givePermissionTo($permissions);
    }

    public function hasPermissionTo($permission): bool
    {
        return $this->permissions->contains(function ($value) use ($permission) {
            return $value->name === $permission || $value->id === $permission;
        });
    }

    protected function getStoredPermission($permission)
    {
        if (is_string($permission)) {
            return app(Permission::class)->findByName($permission, $this->getDefaultGuardName());
        }

        if (is_int($permission)) {
            return app(Permission::class)->findById($permission, $this->getDefaultGuardName());
        }

        return $permission;
    }

    protected static function getRoles(array $params = [])
    {
        return static::where($params)->get();
    }

    protected function getDefaultGuardName(): string
    {
        return config('auth.defaults.guard');
    }

    protected function ensureModelSharesGuard($permissionOrRole)
    {
        if (! $permissionOrRole instanceof Model) {
            return;
        }

        if ($permissionOrRole->guard_name !== $this->guard_name) {
            throw new \Elgaml\Permission\Exceptions\GuardDoesNotMatch();
        }
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
        }
        return $query;
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }
}
