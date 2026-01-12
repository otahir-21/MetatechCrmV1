<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\InternalEmployeeCreateRequest;
use App\Http\Requests\EmployeeInvitationCreateRequest;
use App\Services\InternalEmployeeService;
use App\Services\EmployeeInvitationService;
use App\Services\AuditLogService;
use App\Mail\EmployeeInvitationMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class InternalEmployeeController extends Controller
{
    protected InternalEmployeeService $internalEmployeeService;
    protected EmployeeInvitationService $invitationService;
    protected AuditLogService $auditLogService;

    public function __construct(
        InternalEmployeeService $internalEmployeeService,
        EmployeeInvitationService $invitationService,
        AuditLogService $auditLogService
    ) {
        $this->internalEmployeeService = $internalEmployeeService;
        $this->invitationService = $invitationService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * Create Internal Employee.
     *
     * @param InternalEmployeeCreateRequest $request
     * @return JsonResponse
     */
    public function create(InternalEmployeeCreateRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->canManageInternalEmployees()) {
                return response()->json([
                    'message' => 'Only Product Owner, Internal Super Admin, or Internal Admin can create Internal Employees',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $result = $this->internalEmployeeService->createInternalEmployee(
                $request->validated(),
                $user,
                $request->ip()
            );

            return response()->json([
                'message' => 'Internal Employee created successfully',
                ...$result,
            ], 201);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                409 => 409,
                400 => 400,
                default => 500,
            };

            $errorCode = match ($statusCode) {
                403 => 'INSUFFICIENT_PERMISSIONS',
                409 => 'EMPLOYEE_EXISTS',
                400 => 'VALIDATION_ERROR',
                default => 'INTERNAL_ERROR',
            };

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $errorCode,
            ], $statusCode);
        }
    }

    /**
     * Get list of all internal employees.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->canManageInternalEmployees()) {
                return response()->json([
                    'message' => 'Only Product Owner, Internal Super Admin, or Internal Admin can view internal employees',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $employees = $this->internalEmployeeService->getAllInternalEmployees();

            return response()->json([
                'data' => $employees,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Send employee invitation.
     *
     * @param EmployeeInvitationCreateRequest $request
     * @return JsonResponse
     */
    public function sendInvitation(EmployeeInvitationCreateRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->canManageInternalEmployees()) {
                return response()->json([
                    'message' => 'Only Product Owner, Internal Super Admin, or Internal Admin can send invitations',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            // Prevent non-super_admin from creating super_admin invitations
            if ($request->role === 'super_admin' && !$user->isInternalSuperAdmin()) {
                return response()->json([
                    'message' => 'Only Internal Super Admin can invite other Super Admins',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $invitation = $this->invitationService->createInvitation(
                $request->validated(),
                $user,
                $request->ip()
            );

            // Send invitation email
            Mail::to($invitation->email)->send(
                new EmployeeInvitationMail($invitation, $invitation->plain_token)
            );

            // Log invitation sent
            $this->auditLogService->logInvitation(
                'invitation_sent',
                $user->id,
                null,
                $request->ip(),
                $request->userAgent(),
                [
                    'invitee_email' => $invitation->email,
                    'role' => $invitation->role,
                    'department' => $invitation->department,
                    'designation' => $invitation->designation,
                    'invitation_id' => $invitation->id,
                    'expires_at' => $invitation->expires_at->toIso8601String(),
                ]
            );

            return response()->json([
                'message' => 'Invitation sent successfully',
                'data' => [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'role' => $invitation->role,
                    'department' => $invitation->department,
                    'designation' => $invitation->designation,
                    'expires_at' => $invitation->expires_at->toIso8601String(),
                    'created_at' => $invitation->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                409 => 409,
                400 => 400,
                default => 500,
            };

            $errorCode = match ($statusCode) {
                403 => 'INSUFFICIENT_PERMISSIONS',
                409 => 'INVITATION_EXISTS',
                400 => 'VALIDATION_ERROR',
                default => 'INTERNAL_ERROR',
            };

            // Log the exception for debugging
            \Log::error('Error sending employee invitation: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $errorCode,
            ], $statusCode);
        }
    }

    /**
     * Get pending invitations.
     *
     * @return JsonResponse
     */
    public function getInvitations(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->canManageInternalEmployees()) {
                return response()->json([
                    'message' => 'Only Product Owner, Internal Super Admin, or Internal Admin can view invitations',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $invitations = $this->invitationService->getPendingInvitations();

            return response()->json([
                'data' => $invitations->map(function ($invitation) {
                    return [
                        'id' => $invitation->id,
                        'email' => $invitation->email,
                        'role' => $invitation->role,
                        'department' => $invitation->department,
                        'designation' => $invitation->designation,
                        'invited_by' => $invitation->inviter->name ?? $invitation->inviter->email,
                        'expires_at' => $invitation->expires_at->toIso8601String(),
                        'created_at' => $invitation->created_at->toIso8601String(),
                    ];
                }),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Cancel an invitation.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancelInvitation(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->canManageInternalEmployees()) {
                return response()->json([
                    'message' => 'Only Product Owner, Internal Super Admin, or Internal Admin can cancel invitations',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $invitation = \App\Models\EmployeeInvitation::findOrFail($id);
            
            $this->invitationService->cancelInvitation($id, $user);

            // Log invitation cancelled
            $this->auditLogService->logInvitation(
                'invitation_cancelled',
                $user->id,
                null,
                $request->ip(),
                $request->userAgent(),
                [
                    'invitee_email' => $invitation->email,
                    'role' => $invitation->role,
                    'invitation_id' => $invitation->id,
                ]
            );

            return response()->json([
                'message' => 'Invitation cancelled successfully',
            ], 200);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                404 => 404,
                400 => 400,
                default => 500,
            };

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => match ($statusCode) {
                    403 => 'INSUFFICIENT_PERMISSIONS',
                    404 => 'NOT_FOUND',
                    400 => 'BAD_REQUEST',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }
}
