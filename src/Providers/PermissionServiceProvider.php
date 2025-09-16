<?php
namespace Elgaml\Permission\Providers;

use Elgaml\Permission\PermissionRegistrar;
use Elgaml\Permission\Directives\PermissionBladeDirectives;
use Elgaml\Permission\Contracts\Permission as PermissionContract;
use Elgaml\Permission\Contracts\Role as RoleContract;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerPermissionModels();
        
        $this->app->singleton(PermissionRegistrar::class, function ($app) {
            return new PermissionRegistrar($app['cache']);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->registerPublishables();
        }

        $this->registerMiddleware();
        $this->registerBladeDirectives();
        $this->registerModelBindings();
    }

    protected function registerPermissionModels()
    {
        $config = $this->app->make('config');
        
        $this->app->bind(PermissionContract::class, $config->get('permission.models.permission'));
        $this->app->bind(RoleContract::class, $config->get('permission.models.role'));
    }

    protected function registerCommands()
    {
        $this->commands([
            \Elgaml\Permission\Commands\CachePermissions::class,
            \Elgaml\Permission\Commands\ClearPermissionsCache::class,
            \Elgaml\Permission\Commands\InstallPermissionPackage::class,
            \Elgaml\Permission\Commands\SeedPermissionData::class,
        ]);
    }

    protected function registerPublishables()
    {
        $this->publishes([
            __DIR__.'/../../config/permission.php' => config_path('permission.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../../database/migrations/2025_09_16_000001_create_permission_table.php' => 
            database_path('migrations/'.date('Y_m_d_His', time()).'_create_elgaml_permission_tables.php'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../../database/seeders/PermissionSeeder.php' => 
            database_path('seeders/PermissionSeeder.php'),
        ], 'seeders');
    }

    protected function registerMiddleware()
    {
        $router = $this->app['router'];
        
        $router->aliasMiddleware('role', \Elgaml\Permission\Middlewares\RoleMiddleware::class);
        $router->aliasMiddleware('permission', \Elgaml\Permission\Middlewares\PermissionMiddleware::class);
        $router->aliasMiddleware('role_or_permission', \Elgaml\Permission\Middlewares\RoleOrPermissionMiddleware::class);
    }

    protected function registerBladeDirectives()
    {
        $this->app->afterResolving('blade.compiler', function ($blade) {
            (new PermissionBladeDirectives())->register($blade);
        });
    }

    protected function registerModelBindings()
    {
        $config = $this->app->make('config');
        
        $this->app->bind(PermissionContract::class, $config->get('permission.models.permission'));
        $this->app->bind(RoleContract::class, $config->get('permission.models.role'));
    }
}
