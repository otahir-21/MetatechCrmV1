<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskCreateRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Get tasks for a project.
     *
     * @param int $projectId
     * @param Request $request
     * @return JsonResponse
     */
    public function index(int $projectId, Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $filters = $request->only(['status', 'assigned_to', 'priority', 'is_pinned']);

            $tasks = $this->taskService->getProjectTasks($projectId, $user, $filters);

            return response()->json([
                'data' => $tasks->map(function ($task) {
                    return $this->formatTask($task);
                }),
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
                    403 => 'ACCESS_DENIED',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * Get user's tasks across all projects.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function myTasks(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $filters = $request->only(['status', 'assigned_to_me', 'priority']);

            $tasks = $this->taskService->getUserTasks($user, $filters);

            return response()->json([
                'data' => $tasks->map(function ($task) {
                    return $this->formatTask($task);
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
     * Create a new task.
     * ALL users with project access can create tasks.
     *
     * @param TaskCreateRequest $request
     * @param int $projectId
     * @return JsonResponse
     */
    public function store(TaskCreateRequest $request, int $projectId): JsonResponse
    {
        try {
            $user = auth()->user();
            $task = $this->taskService->createTask($request->validated(), $projectId, $user);

            return response()->json([
                'message' => 'Task created successfully',
                'data' => $this->formatTask($task->load(['assignedTo', 'assignedBy', 'creator'])),
            ], 201);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                404 => 404,
                default => 500,
            };

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => match ($statusCode) {
                    403 => 'ACCESS_DENIED',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * Get a single task.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            $task = $this->taskService->getTask($id, $user);

            return response()->json([
                'data' => $this->formatTask($task),
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
                    403 => 'ACCESS_DENIED',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * Update a task.
     * ALL users with project access can update tasks.
     *
     * @param TaskUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(TaskUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            $task = $this->taskService->updateTask($id, $request->validated(), $user);

            return response()->json([
                'message' => 'Task updated successfully',
                'data' => $this->formatTask($task->load(['assignedTo', 'assignedBy', 'creator'])),
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
                    403 => 'ACCESS_DENIED',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * Delete a task.
     * ALL users with project access can delete tasks.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            $this->taskService->deleteTask($id, $user);

            return response()->json([
                'message' => 'Task deleted successfully',
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
                    403 => 'ACCESS_DENIED',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * Update task position (for drag-and-drop).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updatePosition(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'position' => 'required|integer|min:0',
            ]);

            $user = auth()->user();
            $task = $this->taskService->updateTaskPosition($id, $request->input('position'), $user);

            return response()->json([
                'message' => 'Task position updated successfully',
                'data' => $this->formatTask($task),
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
                    403 => 'ACCESS_DENIED',
                    404 => 'NOT_FOUND',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * Bulk update task positions (for drag-and-drop).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkUpdatePositions(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'tasks' => 'required|array',
                'tasks.*.id' => 'required|integer|exists:tasks,id',
                'tasks.*.position' => 'required|integer|min:0',
            ]);

            $user = auth()->user();
            $this->taskService->bulkUpdateTaskPositions($request->input('tasks'), $user);

            return response()->json([
                'message' => 'Task positions updated successfully',
            ], 200);
        } catch (\Exception $e) {
            $statusCode = match ($e->getCode()) {
                403 => 403,
                default => 500,
            };

            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => match ($statusCode) {
                    403 => 'ACCESS_DENIED',
                    default => 'INTERNAL_ERROR',
                },
            ], $statusCode);
        }
    }

    /**
     * Format task for API response.
     *
     * @param \App\Models\Task $task
     * @return array
     */
    protected function formatTask($task): array
    {
        return [
            'id' => $task->id,
            'project_id' => $task->project_id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'assigned_to' => $task->assignedTo ? [
                'id' => $task->assignedTo->id,
                'name' => $task->assignedTo->name,
                'email' => $task->assignedTo->email,
            ] : null,
            'assigned_by' => $task->assignedBy ? [
                'id' => $task->assignedBy->id,
                'name' => $task->assignedBy->name,
            ] : null,
            'created_by' => [
                'id' => $task->creator->id,
                'name' => $task->creator->name,
                'email' => $task->creator->email,
            ],
            'due_date' => $task->due_date?->toIso8601String(),
            'start_date' => $task->start_date?->toIso8601String(),
            'completed_at' => $task->completed_at?->toIso8601String(),
            'position' => $task->position,
            'tags' => $task->tags ?? [],
            'checklist' => $task->checklist ?? [],
            'is_pinned' => $task->is_pinned,
            'is_overdue' => $task->isOverdue(),
            'is_completed' => $task->isCompleted(),
            'progress_percentage' => $task->getProgressPercentage(),
            'comments_count' => $task->allComments->count(),
            'created_at' => $task->created_at->toIso8601String(),
            'updated_at' => $task->updated_at->toIso8601String(),
        ];
    }
}
