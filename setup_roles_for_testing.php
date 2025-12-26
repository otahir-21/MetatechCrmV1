<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Setting up roles for testing...\n\n";

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create roles
$roles = ['super_admin', 'admin', 'user', 'project_manager'];
foreach ($roles as $roleName) {
    $role = Role::firstOrCreate(['name' => $roleName]);
    echo "✓ Role '{$roleName}' created/exists\n";
}

// Create some permissions
$permissions = [
    'manage users',
    'manage projects',
    'view projects',
    'edit projects',
    'delete projects',
];

foreach ($permissions as $permissionName) {
    $permission = Permission::firstOrCreate(['name' => $permissionName]);
    echo "✓ Permission '{$permissionName}' created/exists\n";
}

// Assign roles to existing users
$productOwner = \App\Models\User::where('email', 'superadmin@productowner.com')->first();
if ($productOwner) {
    $productOwner->assignRole('super_admin');
    echo "✓ Assigned 'super_admin' role to Product Owner\n";
}

// Assign roles to company super admins
$companyAdmins = \App\Models\User::whereNotNull('company_name')
    ->whereNotNull('subdomain')
    ->where('is_metatech_employee', false)
    ->where('role', 'super_admin')
    ->get();

foreach ($companyAdmins as $companyAdmin) {
    $companyAdmin->assignRole('super_admin');
    echo "✓ Assigned 'super_admin' role to Company Admin: {$companyAdmin->email}\n";
}

// Assign admin role to internal employees
$internalEmployees = \App\Models\User::where('is_metatech_employee', true)
    ->where('role', 'admin')
    ->get();

foreach ($internalEmployees as $employee) {
    $employee->assignRole('admin');
    echo "✓ Assigned 'admin' role to Internal Employee: {$employee->email}\n";
}

echo "\n✅ Role setup complete!\n";
echo "\nYou can now test Phase 2 functionality.\n";

