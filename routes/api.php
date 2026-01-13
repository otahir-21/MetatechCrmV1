<?php

use App\Http\Controllers\Api\V1\BootstrapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Bootstrap endpoints
    Route::prefix('bootstrap')->group(function () {
        Route::get('/status', [BootstrapController::class, 'status']);
        Route::post('/create', [BootstrapController::class, 'create']);
        Route::post('/confirm', [BootstrapController::class, 'confirm'])->middleware('auth:api');
        Route::get('/audit', [BootstrapController::class, 'audit'])->middleware('auth:api');
    });

    // Audit Log endpoints (Product Owner only, must be on admincrm subdomain)
    Route::prefix('audit-logs')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\AuditLogController::class, 'index']);
    });

    // Auth endpoints (no subdomain restriction for login endpoint itself)
    Route::post('/auth/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);

    // Company endpoints (Product Owner only, must be on admincrm subdomain)
    Route::prefix('company')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\CompanyController::class, 'index']);
        Route::get('/stats', [\App\Http\Controllers\Api\V1\CompanyController::class, 'stats']);
        Route::get('/{id}', [\App\Http\Controllers\Api\V1\CompanyController::class, 'show']);
        Route::post('/create', [\App\Http\Controllers\Api\V1\CompanyController::class, 'create']);
        Route::put('/{id}/subdomain', [\App\Http\Controllers\Api\V1\CompanyController::class, 'updateSubdomain']);
        Route::get('/invitations', [\App\Http\Controllers\Api\V1\CompanyController::class, 'invitations']);
        Route::delete('/invitations/{id}', [\App\Http\Controllers\Api\V1\CompanyController::class, 'cancelInvitation']);
    });

    // Internal Employee endpoints (Product Owner only, must be on admincrm subdomain)
    Route::prefix('internal-employee')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\InternalEmployeeController::class, 'index']);
        Route::post('/create', [\App\Http\Controllers\Api\V1\InternalEmployeeController::class, 'create']);
        
        // Employee Invitation endpoints
        Route::post('/invite', [\App\Http\Controllers\Api\V1\InternalEmployeeController::class, 'sendInvitation']);
        Route::get('/invitations', [\App\Http\Controllers\Api\V1\InternalEmployeeController::class, 'getInvitations']);
        Route::delete('/invitations/{id}', [\App\Http\Controllers\Api\V1\InternalEmployeeController::class, 'cancelInvitation']);
    });

    // User Management endpoints (Product Owner only, must be on admincrm subdomain)
    Route::prefix('user-management')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
        Route::post('/users/{id}/block', [\App\Http\Controllers\Api\V1\UserManagementController::class, 'blockUser']);
        Route::post('/users/{id}/unblock', [\App\Http\Controllers\Api\V1\UserManagementController::class, 'unblockUser']);
        Route::post('/companies/{id}/block', [\App\Http\Controllers\Api\V1\UserManagementController::class, 'blockCompany']);
        Route::post('/companies/{id}/unblock', [\App\Http\Controllers\Api\V1\UserManagementController::class, 'unblockCompany']);
    });

    // Staff Invitation endpoints (Company Super Admin only, must be on company subdomain)
    Route::prefix('staff')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
        Route::post('/invite', [\App\Http\Controllers\Api\V1\StaffInvitationController::class, 'invite']);
        Route::get('/invitations', [\App\Http\Controllers\Api\V1\StaffInvitationController::class, 'index']);
        Route::delete('/invitations/{id}', [\App\Http\Controllers\Api\V1\StaffInvitationController::class, 'destroy']);
    });

    // Staff Invitation acceptance (public endpoint, no auth required)
    Route::post('/staff/invitations/{token}/accept', [\App\Http\Controllers\Api\V1\StaffInvitationController::class, 'accept']);

    // Project endpoints (Company Super Admin and Staff, must be on company subdomain)
    Route::prefix('projects')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\ProjectController::class, 'index']); // Filtered by user access
        Route::post('/', [\App\Http\Controllers\Api\V1\ProjectController::class, 'store']); // Company Super Admin only
        Route::get('/{id}', [\App\Http\Controllers\Api\V1\ProjectController::class, 'show'])->middleware('project.access');
        Route::put('/{id}', [\App\Http\Controllers\Api\V1\ProjectController::class, 'update'])->middleware('project.access');
        
        // Project Access Management (Company Super Admin and Project Admins)
        Route::post('/{id}/grant-access', [\App\Http\Controllers\Api\V1\ProjectAccessController::class, 'grant'])->middleware('project.access');
        Route::delete('/{id}/revoke-access/{userId}', [\App\Http\Controllers\Api\V1\ProjectAccessController::class, 'revoke'])->middleware('project.access');
        
        // Task endpoints (ALL users with project access can CRUD tasks)
        Route::get('/{id}/tasks', [\App\Http\Controllers\Api\V1\TaskController::class, 'index']);
        Route::post('/{id}/tasks', [\App\Http\Controllers\Api\V1\TaskController::class, 'store']);
    });

    // Task endpoints (standalone)
    Route::prefix('tasks')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
        Route::get('/my-tasks', [\App\Http\Controllers\Api\V1\TaskController::class, 'myTasks']);
        Route::get('/{id}', [\App\Http\Controllers\Api\V1\TaskController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\V1\TaskController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\V1\TaskController::class, 'destroy']);
        Route::post('/{id}/position', [\App\Http\Controllers\Api\V1\TaskController::class, 'updatePosition']);
        Route::post('/bulk-positions', [\App\Http\Controllers\Api\V1\TaskController::class, 'bulkUpdatePositions']);
        Route::patch('/{id}/toggle-internal', [\App\Http\Controllers\Api\V1\SharingController::class, 'toggleTaskInternal']);
        
        // Task Comments
        Route::get('/{id}/comments', [\App\Http\Controllers\Api\V1\TaskCommentController::class, 'index']);
        Route::post('/{id}/comments', [\App\Http\Controllers\Api\V1\TaskCommentController::class, 'store']);
    });

    // Sharing endpoints (Internal employees only)
    Route::prefix('sharing')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
        // Share project resources
        Route::post('/project-resource/share', [\App\Http\Controllers\Api\V1\SharingController::class, 'shareProjectResource']);
        Route::post('/project-resource/revoke', [\App\Http\Controllers\Api\V1\SharingController::class, 'revokeProjectResourceAccess']);
        Route::get('/project-resource/list', [\App\Http\Controllers\Api\V1\SharingController::class, 'getResourceShares']);
        Route::post('/project-resource/bulk-share', [\App\Http\Controllers\Api\V1\SharingController::class, 'bulkShareProjectResource']);
    });

    // Test routes for Phase 2 (Role-based access)
    Route::prefix('test')->middleware(['auth:api', 'subdomain.verify'])->group(function () {
        Route::get('/super-admin-only', function() {
            return response()->json(['message' => 'Access granted - Super Admin only']);
        })->middleware('role:super_admin');
        
        Route::get('/admin-or-super', function() {
            return response()->json(['message' => 'Access granted - Admin or Super Admin']);
        })->middleware('role:admin,super_admin');
    });
});

