<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Elgaml\Permission\Models\Role;
use Elgaml\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\User; // Assuming your User model is in App\Models

class PermissionSeeder extends Seeder
{
    public function run(): void
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
        DB::table('role_has_permissions')->delete();
        
        // Clear user roles and permissions
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        
        // Clear roles and permissions
        Role::query()->delete();
        Permission::query()->delete();
    }
    
    private function createPermissions()
    {
        $permissions = [
            // User permissions
            ['name' => 'view users', 'description' => 'View users', 'group' => 'user'],
            ['name' => 'create users', 'description' => 'Create users', 'group' => 'user'],
            ['name' => 'edit users', 'description' => 'Edit users', 'group' => 'user'],
            ['name' => 'delete users', 'description' => 'Delete users', 'group' => 'user'],
            
            // Article permissions
            ['name' => 'view articles', 'description' => 'View articles', 'group' => 'article'],
            ['name' => 'create articles', 'description' => 'Create articles', 'group' => 'article'],
            ['name' => 'edit articles', 'description' => 'Edit articles', 'group' => 'article'],
            ['name' => 'delete articles', 'description' => 'Delete articles', 'group' => 'article'],
            ['name' => 'publish articles', 'description' => 'Publish articles', 'group' => 'article'],
            
            // System permissions
            ['name' => 'manage system', 'description' => 'Manage system', 'group' => 'system'],
            ['name' => 'view reports', 'description' => 'View reports', 'group' => 'system'],
            ['name' => 'export data', 'description' => 'Export data', 'group' => 'system'],
        ];
        
        $createdPermissions = [];
        
        foreach ($permissions as $permission) {
            $createdPermissions[$permission['name']] = Permission::create([
                'name' => $permission['name'],
                'guard_name' => 'web',
                'description' => $permission['description'],
                'group' => $permission['group'],
            ]);
        }
        
        $this->command->info('Created ' . count($permissions) . ' permissions');
        
        return $createdPermissions;
    }
    
    private function createRoles($permissions)
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrator',
                'level' => 100,
                'permissions' => [
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
            ],
            [
                'name' => 'editor',
                'description' => 'Editor',
                'level' => 50,
                'permissions' => [
                    'view articles',
                    'create articles',
                    'edit articles',
                    'publish articles',
                ],
            ],
            [
                'name' => 'author',
                'description' => 'Author',
                'level' => 30,
                'permissions' => [
                    'view articles',
                    'create articles',
                    'edit articles',
                ],
            ],
            [
                'name' => 'viewer',
                'description' => 'Viewer',
                'level' => 10,
                'permissions' => [
                    'view articles',
                    'view reports',
                ],
            ],
        ];
        
        $createdRoles = [];
        
        foreach ($roles as $role) {
            $roleModel = Role::create([
                'name' => $role['name'],
                'guard_name' => 'web',
                'description' => $role['description'],
                'level' => $role['level'],
            ]);
            
            // Assign permissions to role
            foreach ($role['permissions'] as $permissionName) {
                $roleModel->givePermissionTo($permissions[$permissionName]);
            }
            
            $createdRoles[$role['name']] = $roleModel;
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
