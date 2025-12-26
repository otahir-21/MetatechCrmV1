<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffInvitationCreateRequest;
use App\Http\Requests\StaffInvitationAcceptRequest;
use App\Services\StaffInvitationService;
use App\Mail\StaffInvitationMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class StaffInvitationController extends Controller
{
    protected StaffInvitationService $staffInvitationService;

    public function __construct(StaffInvitationService $staffInvitationService)
    {
        $this->staffInvitationService = $staffInvitationService;
    }

    /**
     * Invite staff member.
     *
     * @param StaffInvitationCreateRequest $request
     * @return JsonResponse
     */
    public function invite(StaffInvitationCreateRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isCompanySuperAdmin()) {
                return response()->json([
                    'message' => 'Only Company Super Admin can invite staff',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $invitation = $this->staffInvitationService->inviteStaff($request->validated(), $user);

            // Send invitation email
            try {
                Mail::to($invitation->email)->send(new StaffInvitationMail($invitation));
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Log::error('Failed to send invitation email: ' . $e->getMessage());
                // Still return success as invitation was created
            }

            return response()->json([
                'message' => 'Invitation sent successfully',
                'data' => [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'role' => $invitation->role,
                    'status' => $invitation->status,
                    'expires_at' => $invitation->expires_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                409 => 409,
                404 => 404,
                default => 500,
            };

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => match ($statusCode) {
                    403 => 'INSUFFICIENT_PERMISSIONS',
                    409 => 'DUPLICATE_INVITATION',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * List all invitations for the company.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isCompanySuperAdmin()) {
                return response()->json([
                    'message' => 'Only Company Super Admin can view invitations',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $invitations = $this->staffInvitationService->getCompanyInvitations($user);

            return response()->json([
                'data' => $invitations->map(function ($invitation) {
                    return [
                        'id' => $invitation->id,
                        'email' => $invitation->email,
                        'role' => $invitation->role,
                        'status' => $invitation->status,
                        'expires_at' => $invitation->expires_at->toIso8601String(),
                        'accepted_at' => $invitation->accepted_at?->toIso8601String(),
                        'created_at' => $invitation->created_at->toIso8601String(),
                        'invited_by' => $invitation->invitedBy->name ?? $invitation->invitedBy->email,
                    ];
                }),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Accept invitation.
     *
     * @param StaffInvitationAcceptRequest $request
     * @param string $token
     * @return JsonResponse
     */
    public function accept(StaffInvitationAcceptRequest $request, string $token): JsonResponse
    {
        try {
            $user = $this->staffInvitationService->acceptInvitation($token, $request->validated());

            return response()->json([
                'message' => 'Invitation accepted successfully. You can now login.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'role' => $user->role,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                409 => 409,
                404 => 404,
                default => 500,
            };

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => match ($statusCode) {
                    409 => 'USER_EXISTS',
                    404 => 'INVITATION_NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * Cancel invitation.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isCompanySuperAdmin()) {
                return response()->json([
                    'message' => 'Only Company Super Admin can cancel invitations',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $this->staffInvitationService->cancelInvitation($id, $user);

            return response()->json([
                'message' => 'Invitation cancelled successfully',
            ], 200);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                404 => 404,
                default => 500,
            };

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => match ($statusCode) {
                    403 => 'INSUFFICIENT_PERMISSIONS',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }
}
