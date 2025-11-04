<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $admin = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'permissions' => json_encode([
                'manage_users',
                'view_all_clients',
                'delete_records',
                'manage_all_leads',
                'manage_all_tickets'
            ])
        ]);

        $manager = Role::create([
            'name' => 'Manager',
            'slug' => 'manager',
            'permissions' => json_encode([
                'view_all_clients',
                'edit_clients',
                'assign_leads',
                'view_all_leads',
                'view_all_tickets'
            ])
        ]);

        $sales = Role::create([
            'name' => 'Sales Executive',
            'slug' => 'sales',
            'permissions' => json_encode([
                'view_own_leads',
                'add_follow_ups',
                'create_clients'
            ])
        ]);

        $support = Role::create([
            'name' => 'Support Staff',
            'slug' => 'support',
            'permissions' => json_encode([
                'view_tickets',
                'update_ticket_status'
            ])
        ]);

        // Create demo users for each role

        // Admin User
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@crm.com',
            'password' => Hash::make('password123'),
        ]);
        $adminUser->roles()->attach($admin->id);

        // Manager User
        $managerUser = User::create([
            'name' => 'Manager User',
            'email' => 'manager@crm.com',
            'password' => Hash::make('password123'),
        ]);
        $managerUser->roles()->attach($manager->id);

        // Sales User
        $salesUser = User::create([
            'name' => 'Sales Executive',
            'email' => 'sales@crm.com',
            'password' => Hash::make('password123'),
        ]);
        $salesUser->roles()->attach($sales->id);

        // Support User
        $supportUser = User::create([
            'name' => 'Support Staff',
            'email' => 'support@crm.com',
            'password' => Hash::make('password123'),
        ]);
        $supportUser->roles()->attach($support->id);

        $this->command->info('Roles and demo users created successfully!');
        $this->command->info('Admin: admin@crm.com / password123');
        $this->command->info('Manager: manager@crm.com / password123');
        $this->command->info('Sales: sales@crm.com / password123');
        $this->command->info('Support: support@crm.com / password123');
    }
}
