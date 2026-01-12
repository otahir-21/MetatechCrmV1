<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    /**
     * Determine if the user can view any deals.
     */
    public function viewAny(User $user): bool
    {
        // Only Metatech employees can access deals
        return $user->is_metatech_employee;
    }

    /**
     * Determine if the user can view the deal.
     */
    public function view(User $user, Deal $deal): bool
    {
        // Admins and Super Admins can view all deals
        if (in_array($user->role, ['admin', 'super_admin']) && $user->is_metatech_employee) {
            return true;
        }

        // Sales agents (users) can only view their own deals
        return $user->is_metatech_employee && $deal->assigned_to === $user->id;
    }

    /**
     * Determine if the user can create deals.
     */
    public function create(User $user): bool
    {
        return $user->is_metatech_employee;
    }

    /**
     * Determine if the user can update the deal.
     */
    public function update(User $user, Deal $deal): bool
    {
        // Admins and Super Admins can update all deals
        if (in_array($user->role, ['admin', 'super_admin']) && $user->is_metatech_employee) {
            return true;
        }

        // Sales agents can only update their own deals
        return $user->is_metatech_employee && $deal->assigned_to === $user->id;
    }

    /**
     * Determine if the user can delete the deal.
     */
    public function delete(User $user, Deal $deal): bool
    {
        // Only admins and super admins can delete deals
        return in_array($user->role, ['admin', 'super_admin']) && $user->is_metatech_employee;
    }

    /**
     * Determine if the user can restore the deal.
     */
    public function restore(User $user, Deal $deal): bool
    {
        return in_array($user->role, ['admin', 'super_admin']) && $user->is_metatech_employee;
    }

    /**
     * Determine if the user can permanently delete the deal.
     */
    public function forceDelete(User $user, Deal $deal): bool
    {
        return $user->role === 'super_admin' && $user->is_metatech_employee;
    }
}
