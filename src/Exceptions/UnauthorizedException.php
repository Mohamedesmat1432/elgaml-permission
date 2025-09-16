<?php
namespace Elgaml\Permission\Exceptions;

use Exception;
use Illuminate\Support\Collection;

class UnauthorizedException extends Exception
{
    protected $requiredRoles;
    protected $requiredPermissions;

    public static function forRoles(array|string $roles): self
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $message = 'User does not have the right roles. Necessary roles are: ' . implode(', ', $roles);

        return new static($message, 403, null, $roles);
    }

    public static function forPermissions(array|string $permissions): self
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];
        $message = 'User does not have the right permissions. Necessary permissions are: ' . implode(', ', $permissions);

        return new static($message, 403, null, [], $permissions);
    }

    public static function forRolesOrPermissions(array|string $rolesOrPermissions): self
    {
        $rolesOrPermissions = is_array($rolesOrPermissions) ? $rolesOrPermissions : [$rolesOrPermissions];
        $message = 'User does not have any of the necessary roles or permissions. Required roles or permissions are: ' . implode(', ', $rolesOrPermissions);

        return new static($message, 403, null, $rolesOrPermissions, $rolesOrPermissions);
    }

    public static function notLoggedIn(): self
    {
        return new static('User is not logged in.', 403);
    }

    public function __construct(string $message = '', int $code = 403, ?Throwable $previous = null, array $roles = [], array $permissions = [])
    {
        parent::__construct($message, $code, $previous);
        $this->requiredRoles = $roles;
        $this->requiredPermissions = $permissions;
    }

    public function getRequiredRoles(): array
    {
        return $this->requiredRoles;
    }

    public function getRequiredPermissions(): array
    {
        return $this->requiredPermissions;
    }
}
