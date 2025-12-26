<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InternalEmployeeService
{
    /**
     * Create Internal Metatech Employee.
     *
     * @param array $data
     * @param User $creator
     * @param string $ipAddress
     * @return array
     * @throws \Exception
     */
    public function createInternalEmployee(array $data, User $creator, string $ipAddress): array
    {
        if (!$creator->canManageInternalEmployees()) {
            throw new \Exception('Only Product Owner, Internal Super Admin, or Internal Admin can create Internal Employees', 403);
        }

        return DB::transaction(function () use ($data) {
            // Check if email already exists
            $existingUser = User::where('email', strtolower($data['email']))->first();
            if ($existingUser) {
                throw new \Exception('Email already taken', 400);
            }

            // Create Internal Employee
            $user = User::create([
                'email' => strtolower(trim($data['email'])),
                'password' => Hash::make($data['password']),
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'name' => trim($data['first_name']) . ' ' . trim($data['last_name']),
                'role' => $data['role'] ?? 'user',
                'is_metatech_employee' => true,  // Mark as internal employee
                'company_name' => null,  // Must be null for internal employees
                'subdomain' => null,     // Must be null for internal employees
                'department' => $data['department'] ?? null,
                'designation' => $data['designation'] ?? null,
                'joined_date' => isset($data['joined_date']) ? $data['joined_date'] : null,
                'status' => 'active', // Default to active
                'email_verified_at' => null,
            ]);

            return [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role' => $user->role,
                    'department' => $user->department,
                    'designation' => $user->designation,
                    'joined_date' => $user->joined_date?->format('Y-m-d'),
                    'is_metatech_employee' => $user->is_metatech_employee,
                    'status' => $user->status,
                    'email_verified_at' => null,
                    'created_at' => $user->created_at->toIso8601String(),
                ],
            ];
        });
    }

    /**
     * Get list of all internal employees.
     *
     * @return array
     */
    public function getAllInternalEmployees(): array
    {
        $employees = User::where('is_metatech_employee', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return $employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'email' => $employee->email,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'name' => $employee->name,
                'role' => $employee->role,
                'department' => $employee->department,
                'designation' => $employee->designation,
                'joined_date' => $employee->joined_date?->format('Y-m-d'),
                'status' => $employee->status ?? 'active',
                'created_at' => $employee->created_at->toIso8601String(),
            ];
        })->toArray();
    }
}

