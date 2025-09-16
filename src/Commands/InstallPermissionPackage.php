<?php
namespace Elgaml\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallPermissionPackage extends Command
{
    protected $signature = 'permission:install';
    protected $description = 'Install the Elgaml Permission Package';

    public function handle()
    {
        $this->info('Installing Elgaml Permission Package...');
        
        // Publish config
        $this->call('vendor:publish', [
            '--provider' => "Elgaml\Permission\Providers\PermissionServiceProvider",
            '--tag' => 'config'
        ]);
        
        // Create migration file
        $migrationPath = database_path('migrations/2025_09_16_000002_create_permission_table.php');
        File::copy(__DIR__.'/../../database/migrations/2025_09_16_000001_create_permission_table.php', $migrationPath);
        
        $this->info('Migration && config published successfully!');
        $this->info('Please run:');
        $this->info('1. php artisan migrate');
        $this->info('2. php artisan permission:seed');
        $this->info('3. php artisan permission:cache');
    }
}
