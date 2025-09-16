<?php
return [
    'models' => [
        'permission' => Elgaml\Permission\Models\Permission::class,
        'role' => Elgaml\Permission\Models\Role::class,
    ],
    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'user_has_permissions' => 'user_has_permissions',
        'user_has_roles' => 'user_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],
    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'elgaml.permission.permissions',
    ],
];
