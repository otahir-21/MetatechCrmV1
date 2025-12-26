<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyCreateRequest;
use App\Http\Requests\CompanyUpdateSubdomainRequest;
use App\Services\CompanyService;
use App\Services\CompanyOwnerInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected CompanyService $companyService;
    protected CompanyOwnerInvitationService $invitationService;

    public function __construct(CompanyService $companyService, CompanyOwnerInvitationService $invitationService)
    {
        $this->companyService = $companyService;
        $this->invitationService = $invitationService;
    }

    /**
     * Create Company Super Admin.
     *
     * @param CompanyCreateRequest $request
     * @return JsonResponse
     */
    public function create(CompanyCreateRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can create Company Super Admin',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $result = $this->companyService->createCompanyAndSendInvitation(
                $request->validated(),
                $user,
                $request->ip()
            );

            return response()->json([
                'message' => 'Company created and invitation sent successfully. The Company Owner will receive an email to activate their account.',
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
                409 => 'COMPANY_EXISTS',
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
     * Get list of all companies.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can view companies',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $companies = $this->companyService->getAllCompanies();

            return response()->json([
                'data' => $companies,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Get company details.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can view company details',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $company = $this->companyService->getCompanyDetails($id);

            return response()->json([
                'data' => $company,
            ], 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $statusCode === 404 ? 'NOT_FOUND' : 'INTERNAL_ERROR',
            ], $statusCode);
        }
    }

    /**
     * Get companies statistics.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can view statistics',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $stats = $this->companyService->getCompaniesStats();

            return response()->json([
                'data' => $stats,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Update company subdomain.
     *
     * @param int $id
     * @param CompanyUpdateSubdomainRequest $request
     * @return JsonResponse
     */
    public function updateSubdomain(int $id, CompanyUpdateSubdomainRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can update company subdomain',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $result = $this->companyService->updateCompanySubdomain(
                $id,
                $request->validated()['subdomain'],
                $user
            );

            return response()->json([
                'message' => 'Company subdomain updated successfully',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                404 => 404,
                409 => 409,
                400 => 400,
                default => 500,
            };

            $errorCode = match ($statusCode) {
                403 => 'INSUFFICIENT_PERMISSIONS',
                404 => 'NOT_FOUND',
                409 => 'SUBDOMAIN_EXISTS',
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
     * Get all company owner invitations.
     *
     * @return JsonResponse
     */
    public function invitations(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can view invitations',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $invitations = $this->invitationService->getAllInvitations($user);

            return response()->json([
                'data' => $invitations->map(function ($invitation) {
                    return [
                        'id' => $invitation->id,
                        'email' => $invitation->email,
                        'company_name' => $invitation->company_name,
                        'subdomain' => $invitation->subdomain,
                        'first_name' => $invitation->first_name,
                        'last_name' => $invitation->last_name,
                        'status' => $invitation->isAccepted() ? 'accepted' : ($invitation->isExpired() ? 'expired' : 'pending'),
                        'accepted' => $invitation->accepted,
                        'accepted_at' => $invitation->accepted_at?->toIso8601String(),
                        'expires_at' => $invitation->expires_at?->toIso8601String(),
                        'created_at' => $invitation->created_at->toIso8601String(),
                        'invited_by' => $invitation->inviter ? [
                            'id' => $invitation->inviter->id,
                            'email' => $invitation->inviter->email,
                        ] : null,
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
     * Cancel a company owner invitation.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancelInvitation(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can cancel invitations',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $this->invitationService->cancelInvitation($id, $user);

            return response()->json([
                'message' => 'Invitation cancelled successfully',
            ], 200);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                400 => 400,
                404 => 404,
                default => 500,
            };

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $statusCode === 404 ? 'NOT_FOUND' : ($statusCode === 400 ? 'BAD_REQUEST' : 'INTERNAL_ERROR'),
            ], $statusCode);
        }
    }
}
