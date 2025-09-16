<?php
return [
    'models' => [
        'permission' => Elgaml\Permission\Models\Permission::class,
        'role' => Elgaml\Permission\Models\Role::class,
    ],
    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_roles' => 'model_has_roles',
        'model_has_permissions' => 'model_has_permissions',
        'role_has_permissions' => 'role_has_permissions',
    ],
    'column_names' => [
        'model_morph_key' => 'model_id',
    ],
    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'elgaml.permission.cache',
    ],
    'register_permission_check_method' => true,
    'teams' => false,
    'wildcard_permissions' => false,
    'wildcard_separator' => '.',
];
