# Elgaml Laravel Permission Package

A Laravel package for role and permission management, similar to Spatie's Laravel Permission.

## Installation

1. Install the package via composer:
```bash
composer require elgaml/permission

2. Publish the configuration file:

php artisan vendor:publish --provider="Elgaml\Permission\Providers\PermissionServiceProvider"

3. Run the migrations: 

php artisan migrate

*** Usage *** 

1. Add the trait to your User model 

use Elgaml\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}

2. Assign roles and permissions

$user->assignRole('admin');
$user->givePermissionTo('edit articles');

3. Check permissions

if ($user->hasPermissionTo('edit articles')) {
    // User can edit articles
}

4. Middleware

Route::group(['middleware' => ['role:admin']], function () {
    // Admin routes
});

Route::group(['middleware' => ['permission:edit articles']], function () {
    // Routes requiring permission
});

5. Blade directives 

@role('admin')
    <div>Admin Content</div>
@endrole

@can('edit articles')
    <button>Edit Article</button>
@endcan

6. Caching

php artisan permission:cache
php artisan permission:clear-cache
