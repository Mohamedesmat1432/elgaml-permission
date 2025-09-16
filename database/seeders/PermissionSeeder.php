<?php

namespace Elgaml\Permission\Database\Seeders;

use Illuminate\Database\Seeder;
use Elgaml\Permission\Models\Role;
use Elgaml\Permission\Models\Permission;
use App\Models\User; // Assuming your User model is in App\Models

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        $this->clearData();
        
        // Create permissions
        $permissions = $this->createPermissions();
        
        // Create roles
        $roles = $this->createRoles($permissions);
        
        // Create users
        $users = $this->createUsers($roles, $permissions);
        
        // Test relationships and methods
        $this->testRelationships($users, $roles, $permissions);
        
        $this->command->info('Permission package seeded successfully!');
    }
    
    private function clearData()
    {
        // Clear role permissions
        \DB::table('role_has_permissions')->delete();
        
        // Clear user roles and permissions
        \DB::table('user_has_roles')->delete();
        \DB::table('user_has_permissions')->delete();
        
        // Clear roles and permissions
        Role::query()->delete();
        Permission::query()->delete();
        
        // Clear users (optional - be careful in production)
        // User::query()->delete();
    }
    
    private function createPermissions()
    {
        $permissions = [
            // User permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Article permissions
            'view articles',
            'create articles',
            'edit articles',
            'delete articles',
            'publish articles',
            
            // System permissions
            'manage system',
            'view reports',
            'export data',
        ];
        
        $createdPermissions = [];
        
        foreach ($permissions as $permission) {
            $createdPermissions[$permission] = Permission::create(['name' => $permission]);
        }
        
        $this->command->info('Created ' . count($permissions) . ' permissions');
        
        return $createdPermissions;
    }
    
    private function createRoles($permissions)
    {
        $roles = [
            'admin' => [
                'view users',
                'create users',
                'edit users',
                'delete users',
                'view articles',
                'create articles',
                'edit articles',
                'delete articles',
                'publish articles',
                'manage system',
                'view reports',
                'export data',
            ],
            'editor' => [
                'view articles',
                'create articles',
                'edit articles',
                'publish articles',
            ],
            'author' => [
                'view articles',
                'create articles',
                'edit articles',
            ],
            'viewer' => [
                'view articles',
                'view reports',
            ],
        ];
        
        $createdRoles = [];
        
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::create(['name' => $roleName]);
            
            // Assign permissions to role
            foreach ($rolePermissions as $permissionName) {
                $role->givePermissionTo($permissions[$permissionName]);
            }
            
            $createdRoles[$roleName] = $role;
        }
        
        $this->command->info('Created ' . count($roles) . ' roles');
        
        return $createdRoles;
    }
    
    private function createUsers($roles, $permissions)
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'roles' => ['admin'],
                'permissions' => [],
            ],
            [
                'name' => 'Editor User',
                'email' => 'editor@example.com',
                'password' => bcrypt('password'),
                'roles' => ['editor'],
                'permissions' => ['export data'], // Direct permission
            ],
            [
                'name' => 'Author User',
                'email' => 'author@example.com',
                'password' => bcrypt('password'),
                'roles' => ['author'],
                'permissions' => [],
            ],
            [
                'name' => 'Viewer User',
                'email' => 'viewer@example.com',
                'password' => bcrypt('password'),
                'roles' => ['viewer'],
                'permissions' => [],
            ],
            [
                'name' => 'Super User',
                'email' => 'super@example.com',
                'password' => bcrypt('password'),
                'roles' => ['admin', 'editor'], // Multiple roles
                'permissions' => ['manage system'], // Direct permission
            ],
        ];
        
        $createdUsers = [];
        
        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
            ]);
            
            // Assign roles
            foreach ($userData['roles'] as $roleName) {
                $user->assignRole($roles[$roleName]);
            }
            
            // Assign direct permissions
            foreach ($userData['permissions'] as $permissionName) {
                $user->givePermissionTo($permissions[$permissionName]);
            }
            
            $createdUsers[$userData['email']] = $user;
        }
        
        $this->command->info('Created ' . count($users) . ' users');
        
        return $createdUsers;
    }
    
    private function testRelationships($users, $roles, $permissions)
    {
        $this->command->info('Testing relationships and methods...');
        
        // Test User-Role relationships
        $adminUser = $users['admin@example.com'];
        $this->command->info("Admin User Roles: " . $adminUser->roles->pluck('name')->implode(', '));
        
        // Test User-Permission relationships
        $this->command->info("Admin User Permissions: " . $adminUser->getAllPermissions()->pluck('name')->implode(', '));
        
        // Test Role-Permission relationships
        $editorRole = $roles['editor'];
        $this->command->info("Editor Role Permissions: " . $editorRole->permissions->pluck('name')->implode(', '));
        
        // Test hasRole method
        $this->command->info("Admin has admin role: " . ($adminUser->hasRole('admin') ? 'Yes' : 'No'));
        $this->command->info("Admin has editor role: " . ($adminUser->hasRole('editor') ? 'Yes' : 'No'));
        
        // Test hasPermissionTo method
        $this->command->info("Admin can delete users: " . ($adminUser->hasPermissionTo('delete users') ? 'Yes' : 'No'));
        $this->command->info("Admin can create articles: " . ($adminUser->hasPermissionTo('create articles') ? 'Yes' : 'No'));
        
        // Test direct permissions
        $editorUser = $users['editor@example.com'];
        $this->command->info("Editor can export data: " . ($editorUser->hasPermissionTo('export data') ? 'Yes' : 'No'));
        
        // Test multiple roles
        $superUser = $users['super@example.com'];
        $this->command->info("Super User roles: " . $superUser->roles->pluck('name')->implode(', '));
        $this->command->info("Super User can manage system: " . ($superUser->hasPermissionTo('manage system') ? 'Yes' : 'No'));
        
        // Test permission inheritance through roles
        $authorUser = $users['author@example.com'];
        $this->command->info("Author can edit articles: " . ($authorUser->hasPermissionTo('edit articles') ? 'Yes' : 'No'));
        $this->command->info("Author can delete articles: " . ($authorUser->hasPermissionTo('delete articles') ? 'Yes' : 'No'));
        
        // Test blade directives (simulated)
        $this->command->info("Blade directive test for admin role: " . ($adminUser->hasRole('admin') ? 'Pass' : 'Fail'));
        $this->command->info("Blade directive test for edit permission: " . ($adminUser->hasPermissionTo('edit articles') ? 'Pass' : 'Fail'));
        
        $this->command->info('All tests completed successfully!');
    }
}
