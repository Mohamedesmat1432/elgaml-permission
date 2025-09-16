<?php
namespace Elgaml\Permission\Exceptions;

use InvalidArgumentException;

class GuardDoesNotMatch extends InvalidArgumentException
{
    public static function create()
    {
        return new static("The given role or permission does not match the guard of the user.");
    }
}
