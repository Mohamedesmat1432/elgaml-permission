<?php
namespace Elgaml\Permission\Helpers;

class PermissionHelper
{
    public static function getWildcardPermissions(): array
    {
        return config('permission.wildcard_permissions', false);
    }

    public static function getWildcardSeparator(): string
    {
        return config('permission.wildcard_separator', '.');
    }

    public static function isWildcardPermission(string $permission): bool
    {
        if (!static::getWildcardPermissions()) {
            return false;
        }

        return strpos($permission, static::getWildcardSeparator()) !== false;
    }

    public static function getWildcardBase(string $permission): string
    {
        $separator = static::getWildcardSeparator();
        $parts = explode($separator, $permission);
        array_pop($parts);

        return implode($separator, $parts);
    }

    public static function matchWildcardPermission(string $requiredPermission, string $givenPermission): bool
    {
        if (!static::isWildcardPermission($requiredPermission)) {
            return $requiredPermission === $givenPermission;
        }

        $base = static::getWildcardBase($requiredPermission);
        return strpos($givenPermission, $base) === 0;
    }
}
