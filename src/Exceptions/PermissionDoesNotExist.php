<?php
namespace Elgaml\Permission\Exceptions;

use InvalidArgumentException;

class PermissionDoesNotExist extends InvalidArgumentException
{
    public static function create(string $permissionName, string $guardName = null)
    {
        return new static("There is no permission named `{$permissionName}` for guard `{$guardName}`.");
    }

    public static function withId(int $permissionId, string $guardName = null)
    {
        return new static("There is no permission with id `{$permissionId}` for guard `{$guardName}`.");
    }
}
