<?php
namespace Elgaml\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Elgaml\Permission\Traits\RefreshesPermissionCache;
use Elgaml\Permission\Contracts\Permission as PermissionContract;
use Elgaml\Permission\Exceptions\PermissionDoesNotExist;
use Elgaml\Permission\Helpers\PermissionHelper;

class Permission extends Model implements PermissionContract
{
    use RefreshesPermissionCache;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'group',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('permission.table_names.permissions'));
    }

    public function roles()
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_permissions'),
            'permission_id',
            'role_id'
        );
    }

    public function users()
    {
        return $this->morphedByMany(
            config('auth.providers.users.model'),
            'model',
            config('permission.table_names.model_has_permissions'),
            'permission_id',
            config('permission.column_names.model_morph_key')
        );
    }

    public static function findByName(string $name, $guardName = null): self
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $permission = static::getPermissions(['name' => $name, 'guard_name' => $guardName])->first();
        
        if (!$permission) {
            throw PermissionDoesNotExist::create($name, $guardName);
        }

        return $permission;
    }

    public static function findById(int $id, $guardName = null): self
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $permission = static::getPermissions(['id' => $id, 'guard_name' => $guardName])->first();

        if (!$permission) {
            throw PermissionDoesNotExist::withId($id, $guardName);
        }

        return $permission;
    }

    public static function findOrCreate(string $name, $guardName = null, $description = null, $group = null): self
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $permission = static::getPermissions(['name' => $name, 'guard_name' => $guardName])->first();

        if (!$permission) {
            return static::query()->create([
                'name' => $name, 
                'guard_name' => $guardName,
                'description' => $description,
                'group' => $group,
            ]);
        }

        return $permission;
    }

    public static function getPermissions(array $params = [])
    {
        return static::where($params)->get();
    }

    public function getGroupAttribute($value)
    {
        return $value ?? 'default';
    }

    public function scopeInGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
        }
        return $query;
    }
}
