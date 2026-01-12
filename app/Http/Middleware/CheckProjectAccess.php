<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ProjectAccessService;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectAccess
{
    protected ProjectAccessService $projectAccessService;

    public function __construct(ProjectAccessService $projectAccessService)
    {
        $this->projectAccessService = $projectAccessService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }
            return redirect('/login');
        }

        // Get project ID from route parameter
        $projectId = $request->route('id') ?? $request->route('projectId') ?? $request->input('project_id');

        if (!$projectId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Project ID required',
                    'error_code' => 'PROJECT_ID_REQUIRED',
                ], 400);
            }
            abort(400, 'Project ID required');
        }

        $user = auth()->user();

        if (!$this->projectAccessService->canUserAccessProject($user, $projectId)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have access to this project',
                    'error_code' => 'ACCESS_DENIED',
                ], 403);
            }
            abort(403, 'You do not have access to this project');
        }

        return $next($request);
    }
}
