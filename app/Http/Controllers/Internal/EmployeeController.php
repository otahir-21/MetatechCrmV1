<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\User;
use App\Services\InternalEmployeeService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    protected InternalEmployeeService $employeeService;
    protected AuditLogService $auditLogService;

    public function __construct(InternalEmployeeService $employeeService, AuditLogService $auditLogService)
    {
        $this->employeeService = $employeeService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * Display a listing of employees.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_metatech_employee || !$user->canManageInternalEmployees()) {
            abort(403, 'Access denied.');
        }

        $query = User::where('is_metatech_employee', true);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by department
        if ($request->has('department') && $request->department) {
            $query->where('department', $request->department);
        }

        // Filter by role (Admin cannot see super_admin)
        if ($user->isInternalAdmin() && !$user->isInternalSuperAdmin()) {
            $query->where('role', '!=', 'super_admin');
        }

        $employees = $query->orderBy('created_at', 'desc')->get();

        // Get unique departments for filter
        $departments = User::where('is_metatech_employee', true)
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        return view('internal.employees.index', [
            'employees' => $employees,
            'departments' => $departments,
            'currentStatus' => $request->status ?? 'all',
            'currentDepartment' => $request->department ?? 'all',
            'canManageStatus' => $user->canManageInternalEmployees(),
        ]);
    }

    /**
     * Display the specified employee.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_metatech_employee) {
            abort(403, 'Access denied.');
        }

        $employee = User::where('is_metatech_employee', true)->findOrFail($id);

        // Users can only view their own profile, Admin/Super Admin can view all
        if ($user->role === 'user' && $employee->id !== $user->id) {
            abort(403, 'Access denied.');
        }

        return view('internal.employees.show', [
            'employee' => $employee,
            'canEdit' => $user->canManageInternalEmployees() && 
                        ($user->isInternalSuperAdmin() || ($user->isInternalAdmin() && $employee->role !== 'super_admin')),
            'canManageStatus' => $user->canManageInternalEmployees(),
        ]);
    }

    /**
     * Show the form for editing the specified employee.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_metatech_employee || !$user->canManageInternalEmployees()) {
            abort(403, 'Access denied.');
        }

        $employee = User::where('is_metatech_employee', true)->findOrFail($id);

        // Admin cannot edit super_admin
        if ($user->isInternalAdmin() && $employee->role === 'super_admin') {
            abort(403, 'You do not have permission to edit this employee.');
        }

        return view('internal.employees.edit', [
            'employee' => $employee,
            'canChangeRole' => $user->isInternalSuperAdmin(),
            'canManageStatus' => $user->canManageInternalEmployees(),
        ]);
    }

    /**
     * Update the specified employee.
     *
     * @param EmployeeUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EmployeeUpdateRequest $request, $id)
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_metatech_employee || !$user->canManageInternalEmployees()) {
            abort(403, 'Access denied.');
        }

        $employee = User::where('is_metatech_employee', true)->findOrFail($id);

        // Admin cannot edit super_admin or change role to super_admin
        if ($user->isInternalAdmin()) {
            if ($employee->role === 'super_admin') {
                abort(403, 'You do not have permission to edit this employee.');
            }
            if ($request->role === 'super_admin') {
                abort(403, 'You do not have permission to assign super_admin role.');
            }
        }

        $oldRole = $employee->role;
        $data = $request->validated();

        // Check if role is being changed
        if ($user->isInternalSuperAdmin() && isset($data['role']) && $data['role'] !== $oldRole) {
            // Update role separately
            $employee->update(['role' => $data['role']]);
            
            // Log role change
            $this->auditLogService->logRoleChange(
                $user->id,
                $employee->id,
                $oldRole,
                $data['role'],
                $request->ip(),
                $request->userAgent(),
                [
                    'target_email' => $employee->email,
                    'changed_by' => $user->name,
                ]
            );
            
            unset($data['role']);
        }

        // Update remaining fields
        if (!empty($data)) {
            $employee->update($data);
        }

        return redirect()->route('internal.employees.show', $employee->id)
            ->with('success', 'Employee updated successfully!');
    }
}
