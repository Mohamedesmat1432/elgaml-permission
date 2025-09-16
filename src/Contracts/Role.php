<?php
namespace Elgaml\Permission\Contracts;

interface Role
{
    public function permissions();
    public function users();
    public function givePermissionTo(...$permissions);
    public function revokePermissionTo($permission);
    public function syncPermissions(...$permissions);
    public function hasPermissionTo($permission): bool;
    public static function findByName(string $name, $guardName = null);
    public static function findById(int $id, $guardName = null);
    public static function findOrCreate(string $name, $guardName = null, $description = null, $level = 0);
}
