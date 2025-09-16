<?php
namespace Elgaml\Permission\Contracts;

interface Permission
{
    public function roles();
    public function users();
    public static function findByName(string $name, $guardName = null);
    public static function findById(int $id, $guardName = null);
    public static function findOrCreate(string $name, $guardName = null, $description = null, $group = null);
}
