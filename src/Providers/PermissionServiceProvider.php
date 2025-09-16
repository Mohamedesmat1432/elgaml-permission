<?php

namespace Elgaml\Permission\Providers;

use Elgaml\Permission\PermissionRegistrar;
use Elgaml\Permission\Directives\PermissionBladeDirectives;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerMiddleware();
        $this->registerCommands();
        $this->registerBladeDirectives();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->mergeConfigFrom(__DIR__ . '/../../config/permission.php', 'permission');
        $this->registerPublishing();
    }

    private function registerPublishing()
    {
        $this->publishes([__DIR__ . '/../../config/permission.php' => config_path('permission.php')], 'config');
        $this->publishes([__DIR__ . '/../../database/migrations' => database_path('migrations')], 'migrations');
        $this->publishes([__DIR__ . '/../../database/seeders/PermissionSeeder.php' => database_path('seeders/PermissionSeeder.php')], 'seeders');
    }

    private function registerMiddleware()
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('role', \Elgaml\Permission\Middlewares\RoleMiddleware::class);
        $router->aliasMiddleware('permission', \Elgaml\Permission\Middlewares\PermissionMiddleware::class);
    }

    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Elgaml\Permission\Commands\CachePermissions::class,
                \Elgaml\Permission\Commands\ClearPermissionsCache::class,
                \Elgaml\Permission\Commands\SeedPermissionData::class,
            ]);
        }
    }

    private function registerBladeDirectives()
    {
        $this->app->afterResolving('blade.compiler', function ($blade) {
            (new PermissionBladeDirectives())->register($blade);
        });
    }
}
