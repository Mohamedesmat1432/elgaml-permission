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
        $migrationPath = database_path('migrations/'.date('Y_m_d_His', time()).'_create_elgaml_permission_tables.php');
        File::copy(__DIR__.'/../../database/migrations/create_permission_tables.php', $migrationPath);
        
        // Publish seeder
        $this->call('vendor:publish', [
            '--provider' => "Elgaml\Permission\Providers\PermissionServiceProvider",
            '--tag' => 'seeders'
        ]);
        
        $this->info('Migration and seeder created successfully!');
        $this->info('Please run:');
        $this->info('1. php artisan migrate');
        $this->info('2. php artisan permission:seed');
        $this->info('3. php artisan permission:cache');
    }
}
