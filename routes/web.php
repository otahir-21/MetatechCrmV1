<?php

use App\Http\Controllers\BootstrapViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BootstrapViewController::class, 'index'])->name('bootstrap.index');
Route::get('/bootstrap/create', [BootstrapViewController::class, 'create'])->name('bootstrap.create');
Route::get('/bootstrap/confirm', [BootstrapViewController::class, 'confirm'])->name('bootstrap.confirm');
Route::get('/bootstrap/complete', [BootstrapViewController::class, 'complete'])->name('bootstrap.complete');

// Auth routes (works for all subdomains, shows appropriate login page)
Route::get('/login', [BootstrapViewController::class, 'showLogin'])->name('login');
Route::get('/internal/login', [BootstrapViewController::class, 'showInternalLogin'])->name('internal.login');
Route::post('/login', [\App\Http\Controllers\Auth\WebLoginController::class, 'login'])->name('login.post');
Route::post('/logout', [\App\Http\Controllers\Auth\WebLoginController::class, 'logout'])->name('logout');

// Password Reset routes (public, no auth required)
Route::get('/password/forgot', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showRequestForm'])->name('password.request');
Route::post('/password/email', [\App\Http\Controllers\Auth\PasswordResetController::class, 'requestReset'])->name('password.email');
Route::get('/password/reset', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resetPassword'])->name('password.update');

// Employee Invitation routes (public, no auth required)
Route::get('/employee/invite/accept', [\App\Http\Controllers\Auth\EmployeeInvitationController::class, 'showAcceptForm'])->name('employee.invite.show');
Route::post('/employee/invite/accept', [\App\Http\Controllers\Auth\EmployeeInvitationController::class, 'acceptInvitation'])->name('employee.invite.accept');

// Dashboard and Company routes (protected, Product Owner only - must be on admincrm subdomain)
Route::middleware(['auth', 'subdomain.verify'])->group(function () {
    Route::get('/dashboard', [BootstrapViewController::class, 'dashboard'])->name('dashboard');
    Route::get('/company/create', [BootstrapViewController::class, 'showCompanyCreate'])->name('company.create');
    Route::get('/internal-employee/create', [\App\Http\Controllers\InternalEmployeeViewController::class, 'showCreateForm'])->name('internal-employee.create');
    Route::get('/audit-logs', [\App\Http\Controllers\AuditLogViewController::class, 'index'])->name('audit-logs.index');
});

// Internal Metatech Employee routes (dashboard only, login handled by main /login route)
Route::prefix('internal')->middleware(['auth', 'subdomain.verify'])->group(function () {
    // Suspended page (accessible even if suspended)
    Route::get('/suspended', function () {
        return view('internal.suspended');
    })->name('internal.suspended');
    
    // All other routes require active status
    Route::middleware(['employee.status'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\InternalDashboardController::class, 'index'])->name('internal.dashboard');
    
    // Projects (Internal CRM)
    Route::get('/projects', [\App\Http\Controllers\Internal\ProjectController::class, 'index'])->name('internal.projects.index');
    Route::get('/projects/create', [\App\Http\Controllers\Internal\ProjectController::class, 'create'])->name('internal.projects.create');
    Route::post('/projects', [\App\Http\Controllers\Internal\ProjectController::class, 'store'])->name('internal.projects.store');
    Route::get('/projects/{id}', [\App\Http\Controllers\Internal\ProjectController::class, 'show'])->name('internal.projects.show');
    Route::get('/projects/{id}/edit', [\App\Http\Controllers\Internal\ProjectController::class, 'edit'])->name('internal.projects.edit');
    Route::put('/projects/{id}', [\App\Http\Controllers\Internal\ProjectController::class, 'update'])->name('internal.projects.update');
    
    // Employees (Internal CRM)
    Route::get('/employees', [\App\Http\Controllers\Internal\EmployeeController::class, 'index'])->name('internal.employees.index');
    Route::get('/employees/{id}', [\App\Http\Controllers\Internal\EmployeeController::class, 'show'])->name('internal.employees.show');
    Route::get('/employees/{id}/edit', [\App\Http\Controllers\Internal\EmployeeController::class, 'edit'])->name('internal.employees.edit');
    Route::put('/employees/{id}', [\App\Http\Controllers\Internal\EmployeeController::class, 'update'])->name('internal.employees.update');
    
    // Clients (Internal CRM - Sales)
    Route::resource('clients', \App\Http\Controllers\Internal\ClientController::class)->names([
        'index' => 'internal.clients.index',
        'create' => 'internal.clients.create',
        'store' => 'internal.clients.store',
        'show' => 'internal.clients.show',
        'edit' => 'internal.clients.edit',
        'update' => 'internal.clients.update',
        'destroy' => 'internal.clients.destroy',
    ]);
    
    // Deals (Internal CRM - Sales Pipeline)
    Route::resource('deals', \App\Http\Controllers\Internal\DealController::class)->names([
        'index' => 'internal.deals.index',
        'create' => 'internal.deals.create',
        'store' => 'internal.deals.store',
        'show' => 'internal.deals.show',
        'edit' => 'internal.deals.edit',
        'update' => 'internal.deals.update',
        'destroy' => 'internal.deals.destroy',
    ]);
    Route::patch('/deals/{deal}/stage', [\App\Http\Controllers\Internal\DealController::class, 'updateStage'])->name('internal.deals.updateStage');
    });
});

// Company Dashboard (Company Super Admin only, must be on company subdomain)
Route::middleware(['auth', 'subdomain.verify'])->group(function () {
    Route::get('/company-dashboard', [\App\Http\Controllers\CompanyDashboardController::class, 'index'])->name('company.dashboard');
});

// Staff Invitation routes (public, no auth required)
Route::get('/accept-invitation/{token}', [\App\Http\Controllers\StaffInvitationViewController::class, 'showAccept'])->name('invitation.accept');
Route::post('/accept-invitation/{token}', [\App\Http\Controllers\StaffInvitationViewController::class, 'accept'])->name('invitation.accept.post');

// Company Owner Invitation routes (public, no auth required)
Route::get('/company-invite/accept', [\App\Http\Controllers\CompanyOwnerInvitationViewController::class, 'showAcceptForm'])->name('company.invite.show');
Route::post('/company-invite/accept', [\App\Http\Controllers\CompanyOwnerInvitationViewController::class, 'acceptInvitation'])->name('company.invite.accept');
