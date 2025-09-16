<?php
namespace Elgaml\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedPermissionData extends Command
{
    protected $signature = 'permission:seed';
    protected $description = 'Seed roles, permissions, and test users';

    public function handle()
    {
        $this->info('Seeding permission data...');
        
        // Run the seeder
        Artisan::call('db:seed', [
            '--class' => 'Elgaml\\\\Permission\\\\Database\\\\Seeders\\\\ElgamlPermissionSeeder'
        ]);
        
        $this->info(Artisan::output());
        
        $this->info('Permission data seeded successfully!');
        $this->info('Test users created:');
        $this->info('Admin: admin@example.com / password');
        $this->info('Editor: editor@example.com / password');
        $this->info('Author: author@example.com / password');
        $this->info('Viewer: viewer@example.com / password');
        $this->info('Super: super@example.com / password');
    }
}
