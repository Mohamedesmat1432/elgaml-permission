# Elgaml Laravel Permission Package

A comprehensive Laravel package for role and permission management, similar to Spatie's Laravel Permission but with additional features.

## Features

- Role and permission management
- Multiple guards support
- Middleware for routes protection
- Blade directives for conditional rendering
- Permission caching for performance
- Wildcard permissions support
- Teams support (multi-tenancy)
- Artisan commands for management
- API and web ready
- Comprehensive testing

## Installation

## Step 1: Install the package via composer:

```bash
composer config repositories.elgaml-permission git https://github.com/Mohamedesmat1432/elgaml-permission.git

composer require elgaml/permission

Step 2: Install the Package:
----------------------------
php artisan permission:install

Step 3: Run the Migrations:
---------------------------
php artisan migrate

Step 4: Seed the Database (Optional):
------------------------------------
php artisan permission:seed

Step 5: Cache Permissions:
--------------------------
php artisan permission:cache

Usage
Step 1: Add the Trait to Your User Model:
----------------------------------------
<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Elgaml\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    // ...
}

Step 2: Assign Roles and Permissions:
-------------------------------------
Assign Roles:
-------------
// Single role
$user->assignRole('admin');
// Multiple roles
$user->assignRole(['admin', 'editor']);

Assign Permissions:
-------------------
// Single permission
$user->givePermissionTo('edit articles');
// Multiple permissions
$user->givePermissionTo(['edit articles', 'delete articles']);

Sync Roles (Replaces Existing Roles):
-------------------------------------
$user->syncRoles('admin');

Sync Permissions (Replaces Existing Permissions):
-------------------------------------------------
$user->syncPermissions('edit articles');

Step 3: Check Permissions:
--------------------------
Check Roles:
------------
// Check if user has a specific role
if ($user->hasRole('admin')) {
    // User is admin
}

// Check if user has any of the specified roles
if ($user->hasAnyRole(['admin', 'editor'])) {
    // User is admin or editor
}

// Check if user has all specified roles
if ($user->hasAllRoles(['admin', 'editor'])) {
    // User is both admin and editor
}

Check Permissions:
------------------
// Check if user has a specific permission
if ($user->hasPermissionTo('edit articles')) {
    // User can edit articles
}

// Check if user has any of the specified permissions
if ($user->hasAnyPermission(['edit articles', 'delete articles'])) {
    // User can edit or delete articles
}

// Check if user has all specified permissions
if ($user->hasAllPermissions(['edit articles', 'delete articles'])) {
    // User can both edit and delete articles
}

Step 4: Protect Routes with Middleware:
--------------------------------------
Role Middleware:
---------------
// In routes/web.php or routes/api.php
Route::group(['middleware' => ['role:admin']], function () {
    Route::get('/admin', 'AdminController@index');
});

Permission Middleware:
----------------------
Route::group(['middleware' => ['permission:edit articles']], function () {
    Route::put('/articles/{id}', 'ArticleController@update');
});

Role or Permission Middleware:
-----------------------------
Route::group(['middleware' => ['role_or_permission:admin|edit articles']], function () {
    Route::get('/articles/create', 'ArticleController@create');
});

Step 5: Use Blade Directives:
-----------------------------
Role Directives:
---------------
@role('admin')
    <div>This content is only visible to admins</div>
@endrole

@hasrole('admin')
    <div>This content is only visible to admins</div>
@else
    <div>This content is visible to non-admins</div>
@endhasrole

@hasanyrole(['admin', 'editor'])
    <div>This content is visible to admins or editors</div>
@endhasanyrole

@hasallroles(['admin', 'editor'])
    <div>This content is visible only to users who are both admins and editors</div>
@endhasallroles


Permission Directives:
----------------------
@can('edit articles')
    <button>Edit Article</button>
@endcan

@canany(['edit articles', 'delete articles'])
    <div>This content is visible to users who can edit or delete articles</div>
@endcanany

@canall(['edit articles', 'delete articles'])
    <div>This content is visible only to users who can both edit and delete articles</div>
@endcanall

@can('edit articles')
    <button>Edit Article</button>
@elsecan('delete articles')
    <button>Delete Article</button>
@else
    <p>No permissions available</p>
@endcan

Role or Permission Directives:
------------------------------
@roleorpermission('admin|edit articles')
    <div>This content is visible to admins or users who can edit articles</div>
@endroleorpermission


Step 6: API Usage:
-----------------
The package works seamlessly with Laravel API routes:
----------------------------------------------------
// In routes/api.php
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', 'AdminController@dashboard');
});

Route::middleware(['auth:api', 'permission:edit articles'])->group(function () {
    Route::put('/api/articles/{id}', 'ArticleController@update');
});


## Advanced Features ##
-----------------------
Wildcard Permissions :
---------------------
To enable wildcard permissions, update your config:
---------------------------------------------------
// config/permission.php
'wildcard_permissions' => true,

Then you can use wildcards:
---------------------------
// User can access any article permission
$user->givePermissionTo('articles.*');

// Check if user has permission to edit articles
$user->hasPermissionTo('articles.edit');


Teams Support:
--------------
To enable teams support, update your config:
--------------------------------------------
// config/permission.php
'teams' => true,

Then you can set the current team:
----------------------------------
app(Elgaml\Permission\PermissionRegistrar::class)->setTeamId($teamId);


Artisan Commands:
----------------
Cache Permissions:
-----------------
php artisan permission:cache

Clear Cache Permissions:
-----------------
php artisan permission:clear-cache

Seed Database with Test Data:
-----------------------------
php artisan permission:seed

This creates:

Roles:

     Admin (level 100)
     Editor (level 50)
     Author (level 30)
     Viewer (level 10)


Permissions:

     User management: view, create, edit, delete
     Article management: view, create, edit, delete, publish
     System: manage system, view reports, export data


Test Users:

     admin@example.com  / password (Admin role)
     editor@example.com  / password (Editor role + direct export permission)
     author@example.com  / password (Author role)
     viewer@example.com  / password (Viewer role)
     super@example.com  / password (Admin + Editor roles + direct system permission)


Configuration :
--------------
After publishing the configuration file, you can customize the package settings in config/permission.php:

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


Troubleshooting:
----------------
Class Not Found Errors

If you encounter class not found errors, run:
--------------------------------------------
composer dump-autoload


Seeder Errors

If you encounter seeder errors, make sure you have:

1. Added the HasRoles trait to your User model
2. Run the migrations before seeding
3. Cleared the cache: php artisan permission:clear-cache

Permission Caching:
------------------
If permissions are not working as expected, try clearing and re-caching:

php artisan permission:clear-cache
php artisan permission:cache


License:
-------
The MIT License (MIT). Please see the License File  for more information:


This README provides a complete guide for using the elgaml/permission package in Laravel 12, with step-by-step instructions and examples for all major features.
```
