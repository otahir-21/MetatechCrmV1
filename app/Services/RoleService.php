<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * Map enum role values to Spatie role names.
     */
    protected const ROLE_MAPPING = [
        'super_admin' => [
            'metatech' => 'metatech.super_admin',
            'client' => 'client.owner',
        ],
        'admin' => [
            'metatech' => 'metatech.admin',
            'client' => 'client.admin',
        ],
        'user' => [
            'metatech' => null, // No default, must be assigned explicitly
            'client' => 'client.staff',
        ],
    ];

    /**
     * Assign role to user based on enum role and user type.
     *
     * @param User $user
     * @param string $enumRole The enum role value (super_admin, admin, user)
     * @param string|null $specificRole Optional specific Spatie role name (e.g., 'metatech.sales')
     * @return void
     */
    public function assignRoleFromEnum(User $user, string $enumRole, ?string $specificRole = null): void
    {
        // Remove any existing roles
        $user->roles()->detach();

        // If specific role is provided, use it
        if ($specificRole) {
            $role = Role::findByName($specificRole, 'web');
            $user->assignRole($role);
            return;
        }

        // Otherwise, map from enum role
        $userType = $user->is_metatech_employee ? 'metatech' : 'client';
        $spatieRoleName = self::ROLE_MAPPING[$enumRole][$userType] ?? null;

        if ($spatieRoleName) {
            $role = Role::findByName($spatieRoleName, 'web');
            $user->assignRole($role);
        }
    }

    /**
     * Assign a specific Spatie role to user.
     *
     * @param User $user
     * @param string $roleName Full role name (e.g., 'metatech.sales', 'client.owner')
     * @return void
     */
    public function assignRole(User $user, string $roleName): void
    {
        $role = Role::findByName($roleName, 'web');
        $user->assignRole($role);
    }

    /**
     * Get available roles for a user type.
     *
     * @param bool $isMetatechEmployee
     * @return array
     */
    public function getAvailableRoles(bool $isMetatechEmployee): array
    {
        if ($isMetatechEmployee) {
            return [
                'metatech.super_admin' => 'Super Admin',
                'metatech.admin' => 'Admin',
                'metatech.executive' => 'Executive',
                'metatech.sales' => 'Sales',
                'metatech.accounts' => 'Accounts',
                'metatech.hr' => 'HR',
                'metatech.design' => 'Design',
                'metatech.development' => 'Development',
                'metatech.marketing' => 'Marketing',
            ];
        }

        return [
            'client.owner' => 'Client Owner',
            'client.admin' => 'Client Admin',
            'client.staff' => 'Client Staff',
        ];
    }

    /**
     * Get role display name.
     *
     * @param string $roleName
     * @return string
     */
    public function getRoleDisplayName(string $roleName): string
    {
        $allRoles = array_merge(
            $this->getAvailableRoles(true),
            $this->getAvailableRoles(false)
        );

        return $allRoles[$roleName] ?? $roleName;
    }

    /**
     * Sync user's enum role field with Spatie role (for backward compatibility).
     *
     * @param User $user
     * @return void
     */
    public function syncEnumRoleFromSpatieRole(User $user): void
    {
        $role = $user->roles->first();
        if (!$role) {
            return;
        }

        $roleName = $role->name;

        // Map Spatie role back to enum role
        if (str_starts_with($roleName, 'metatech.super_admin') || str_starts_with($roleName, 'client.owner')) {
            $user->role = 'super_admin';
        } elseif (str_starts_with($roleName, 'metatech.admin') || str_starts_with($roleName, 'client.admin')) {
            $user->role = 'admin';
        } elseif (str_starts_with($roleName, 'client.staff')) {
            $user->role = 'user';
        } else {
            // For other roles, set to 'admin' as default
            $user->role = 'admin';
        }

        $user->save();
    }
}

