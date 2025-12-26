<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectCreateRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Models\Project;
use App\Models\Company;
use App\Services\ProjectAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected ProjectAccessService $projectAccessService;

    public function __construct(ProjectAccessService $projectAccessService)
    {
        $this->projectAccessService = $projectAccessService;
    }

    /**
     * List projects (filtered by user access).
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }

            // Get user's accessible projects
            $projects = $this->projectAccessService->getUserProjects($user);

            return response()->json([
                'data' => $projects->map(function ($project) use ($user) {
                    $accessLevel = $this->projectAccessService->getUserProjectAccessLevel($user, $project->id);
                    return [
                        'id' => $project->id,
                        'name' => $project->name,
                        'description' => $project->description,
                        'status' => $project->status,
                        'access_level' => $accessLevel,
                        'created_at' => $project->created_at->toIso8601String(),
                        'created_by' => $project->creator->name ?? $project->creator->email,
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
     * Create a new project.
     *
     * @param ProjectCreateRequest $request
     * @return JsonResponse
     */
    public function store(ProjectCreateRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isCompanySuperAdmin()) {
                return response()->json([
                    'message' => 'Only Company Super Admin can create projects',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $company = Company::where('subdomain', $user->subdomain)->firstOrFail();

            $project = Project::create([
                'company_id' => $company->id,
                'name' => $request->validated('name'),
                'description' => $request->validated('description'),
                'status' => 'active',
                'created_by' => $user->id,
            ]);

            // Automatically grant admin access to creator
            $this->projectAccessService->grantProjectAccess($project->id, $user->id, 'admin', $user);

            return response()->json([
                'message' => 'Project created successfully',
                'data' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'created_at' => $project->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Get project details.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }

            // Check if user can access this project
            if (!$this->projectAccessService->canUserAccessProject($user, $id)) {
                return response()->json([
                    'message' => 'You do not have access to this project',
                    'error_code' => 'ACCESS_DENIED',
                ], 403);
            }

            $project = Project::with(['creator', 'users'])->findOrFail($id);
            $accessLevel = $this->projectAccessService->getUserProjectAccessLevel($user, $id);

            return response()->json([
                'data' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'access_level' => $accessLevel,
                    'created_at' => $project->created_at->toIso8601String(),
                    'created_by' => [
                        'id' => $project->creator->id,
                        'name' => $project->creator->name,
                        'email' => $project->creator->email,
                    ],
                    'users' => $project->users->map(function ($user) use ($project) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'access_level' => $user->pivot->access_level,
                            'granted_at' => $user->pivot->granted_at,
                        ];
                    }),
                ],
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
     * Update project.
     *
     * @param ProjectUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ProjectUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }

            // Check if user can access this project and is admin
            $accessLevel = $this->projectAccessService->getUserProjectAccessLevel($user, $id);
            if (!$accessLevel || ($accessLevel !== 'admin' && !$user->isCompanySuperAdmin())) {
                return response()->json([
                    'message' => 'You do not have permission to update this project',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS',
                ], 403);
            }

            $project = Project::findOrFail($id);
            $project->update($request->validated());

            return response()->json([
                'message' => 'Project updated successfully',
                'data' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'updated_at' => $project->updated_at->toIso8601String(),
                ],
            ], 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => $statusCode === 404 ? 'NOT_FOUND' : 'INTERNAL_ERROR',
            ], $statusCode);
        }
    }
}
