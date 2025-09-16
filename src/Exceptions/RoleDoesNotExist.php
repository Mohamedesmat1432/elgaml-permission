<?php
namespace Elgaml\Permission\Exceptions;

use InvalidArgumentException;

class RoleDoesNotExist extends InvalidArgumentException
{
    public static function create(string $roleName, string $guardName = null)
    {
        return new static("There is no role named `{$roleName}` for guard `{$guardName}`.");
    }

    public static function withId(int $roleId, string $guardName = null)
    {
        return new static("There is no role with id `{$roleId}` for guard `{$guardName}`.");
    }
}
