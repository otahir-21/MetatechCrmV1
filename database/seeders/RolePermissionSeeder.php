<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // CREATE PERMISSIONS
        // ============================================

        // Dashboard permissions
        Permission::firstOrCreate(['name' => 'view.dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view.internal.dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view.company.dashboard', 'guard_name' => 'web']);

        // User management permissions
        Permission::firstOrCreate(['name' => 'view.users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create.users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit.users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete.users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage.users', 'guard_name' => 'web']); // Combined permission
        Permission::firstOrCreate(['name' => 'block.users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'invite.users', 'guard_name' => 'web']);

        // Company management permissions (Metatech only)
        Permission::firstOrCreate(['name' => 'view.companies', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create.companies', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit.companies', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete.companies', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage.companies', 'guard_name' => 'web']); // Combined permission
        Permission::firstOrCreate(['name' => 'block.companies', 'guard_name' => 'web']);

        // Project permissions
        Permission::firstOrCreate(['name' => 'view.projects', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create.projects', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit.projects', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete.projects', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage.projects', 'guard_name' => 'web']); // Combined permission

        // Task permissions
        Permission::firstOrCreate(['name' => 'view.tasks', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create.tasks', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit.tasks', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete.tasks', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage.tasks', 'guard_name' => 'web']); // Combined permission
        Permission::firstOrCreate(['name' => 'assign.tasks', 'guard_name' => 'web']);

        // Reports & Analytics permissions
        Permission::firstOrCreate(['name' => 'view.reports', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view.analytics', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'export.reports', 'guard_name' => 'web']);

        // Financial permissions (Accounts team)
        Permission::firstOrCreate(['name' => 'view.financial', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage.financial', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view.invoices', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create.invoices', 'guard_name' => 'web']);

        // Settings permissions
        Permission::firstOrCreate(['name' => 'view.settings', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit.settings', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage.settings', 'guard_name' => 'web']); // Combined permission

        // Audit logs permissions
        Permission::firstOrCreate(['name' => 'view.audit.logs', 'guard_name' => 'web']);

        // Role management permissions
        Permission::firstOrCreate(['name' => 'view.roles', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'assign.roles', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage.roles', 'guard_name' => 'web']); // Combined permission

        // ============================================
        // CREATE METATECH ROLES
        // ============================================

        // Metatech Super Admin - Full access
        $metatechSuperAdmin = Role::firstOrCreate(['name' => 'metatech.super_admin', 'guard_name' => 'web']);
        $metatechSuperAdmin->givePermissionTo(Permission::all());

        // Metatech Admin - Administrative access
        $metatechAdmin = Role::firstOrCreate(['name' => 'metatech.admin', 'guard_name' => 'web']);
        $metatechAdmin->givePermissionTo([
            'view.internal.dashboard',
            'view.users', 'create.users', 'edit.users', 'manage.users', 'invite.users',
            'view.projects', 'create.projects', 'edit.projects', 'manage.projects',
            'view.tasks', 'create.tasks', 'edit.tasks', 'manage.tasks', 'assign.tasks',
            'view.reports', 'view.analytics', 'export.reports',
            'view.settings', 'edit.settings',
            'view.audit.logs',
        ]);

        // Metatech Executive - Executive level access
        $metatechExecutive = Role::firstOrCreate(['name' => 'metatech.executive', 'guard_name' => 'web']);
        $metatechExecutive->givePermissionTo([
            'view.internal.dashboard',
            'view.users',
            'view.companies',
            'view.projects', 'view.tasks',
            'view.reports', 'view.analytics', 'export.reports',
            'view.financial',
            'view.settings',
        ]);

        // Metatech Sales - Sales team access
        $metatechSales = Role::firstOrCreate(['name' => 'metatech.sales', 'guard_name' => 'web']);
        $metatechSales->givePermissionTo([
            'view.internal.dashboard',
            'view.users',
            'view.companies',
            'view.projects', 'view.tasks',
            'view.reports', 'export.reports',
        ]);

        // Metatech Accounts - Finance/Accounting access
        $metatechAccounts = Role::firstOrCreate(['name' => 'metatech.accounts', 'guard_name' => 'web']);
        $metatechAccounts->givePermissionTo([
            'view.internal.dashboard',
            'view.companies',
            'view.financial', 'manage.financial',
            'view.invoices', 'create.invoices',
            'view.reports', 'export.reports',
        ]);

        // Metatech HR - Human Resources access
        $metatechHR = Role::firstOrCreate(['name' => 'metatech.hr', 'guard_name' => 'web']);
        $metatechHR->givePermissionTo([
            'view.internal.dashboard',
            'view.users', 'create.users', 'edit.users', 'invite.users',
            'view.reports',
        ]);

        // Metatech Design - Design team access
        $metatechDesign = Role::firstOrCreate(['name' => 'metatech.design', 'guard_name' => 'web']);
        $metatechDesign->givePermissionTo([
            'view.internal.dashboard',
            'view.projects', 'create.projects', 'edit.projects',
            'view.tasks', 'create.tasks', 'edit.tasks', 'assign.tasks',
        ]);

        // Metatech Development - Development team access
        $metatechDevelopment = Role::firstOrCreate(['name' => 'metatech.development', 'guard_name' => 'web']);
        $metatechDevelopment->givePermissionTo([
            'view.internal.dashboard',
            'view.projects', 'create.projects', 'edit.projects',
            'view.tasks', 'create.tasks', 'edit.tasks', 'assign.tasks',
            'view.reports',
        ]);

        // Metatech Marketing - Marketing team access
        $metatechMarketing = Role::firstOrCreate(['name' => 'metatech.marketing', 'guard_name' => 'web']);
        $metatechMarketing->givePermissionTo([
            'view.internal.dashboard',
            'view.projects', 'view.tasks',
            'view.reports', 'view.analytics', 'export.reports',
        ]);

        // ============================================
        // CREATE CLIENT ROLES
        // ============================================

        // Client Owner - Full company access
        $clientOwner = Role::firstOrCreate(['name' => 'client.owner', 'guard_name' => 'web']);
        $clientOwner->givePermissionTo([
            'view.company.dashboard',
            'view.users', 'create.users', 'edit.users', 'delete.users', 'manage.users', 'invite.users', 'block.users',
            'view.projects', 'create.projects', 'edit.projects', 'delete.projects', 'manage.projects',
            'view.tasks', 'create.tasks', 'edit.tasks', 'delete.tasks', 'manage.tasks', 'assign.tasks',
            'view.reports', 'export.reports',
            'view.settings', 'edit.settings', 'manage.settings',
        ]);

        // Client Admin - Administrative access to company
        $clientAdmin = Role::firstOrCreate(['name' => 'client.admin', 'guard_name' => 'web']);
        $clientAdmin->givePermissionTo([
            'view.company.dashboard',
            'view.users', 'create.users', 'edit.users', 'invite.users',
            'view.projects', 'create.projects', 'edit.projects', 'manage.projects',
            'view.tasks', 'create.tasks', 'edit.tasks', 'manage.tasks', 'assign.tasks',
            'view.reports', 'export.reports',
            'view.settings',
        ]);

        // Client Staff - Regular staff access
        $clientStaff = Role::firstOrCreate(['name' => 'client.staff', 'guard_name' => 'web']);
        $clientStaff->givePermissionTo([
            'view.company.dashboard',
            'view.projects',
            'view.tasks', 'create.tasks', 'edit.tasks',
            'view.reports',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}

