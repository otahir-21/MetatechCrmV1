<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditLogRequest;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
//uploaded image
class AuditLogController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get audit logs (Product Owner only).
     *
     * @param AuditLogRequest $request
     * @return JsonResponse
     */
    public function index(AuditLogRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            // Only Product Owner can view audit logs
            if (!$user || !$user->isProductOwner()) {
                return response()->json([
                    'message' => 'Only Product Owner can view audit logs',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $perPage = min($request->input('per_page', 20), 100);
            
            $filters = [
                'event_type' => $request->input('event_type'),
                'action' => $request->input('action'),
                'user_id' => $request->input('user_id'),
                'target_user_id' => $request->input('target_user_id'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ];

            $logs = $this->auditLogService->getAuditLogs($filters, $perPage);

            return response()->json([
                'data' => $logs->items(),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'last_page' => $logs->lastPage(),
                    'from' => $logs->firstItem(),
                    'to' => $logs->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Audit log error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Internal server error: ' . $e->getMessage(),
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }
}
