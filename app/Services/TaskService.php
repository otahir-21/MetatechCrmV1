<?php

namespace App\Services;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Services\ProjectAccessService;
use Illuminate\Support\Facades\DB;

class TaskService
{
    protected ProjectAccessService $projectAccessService;

    public function __construct(ProjectAccessService $projectAccessService)
    {
        $this->projectAccessService = $projectAccessService;
    }

    /**
     * Create a new task.
     * ALL users with project access can create tasks.
     *
     * @param array $data
     * @param int $projectId
     * @param User $creator
     * @return Task
     * @throws \Exception
     */
    public function createTask(array $data, int $projectId, User $creator): Task
    {
        // Verify user has access to project
        if (!$this->projectAccessService->canUserAccessProject($creator, $projectId)) {
            throw new \Exception('You do not have access to this project', 403);
        }

        // Get max position for ordering
        $maxPosition = Task::where('project_id', $projectId)->max('position') ?? 0;

        return Task::create([
            'project_id' => $projectId,
            'title' => trim($data['title']),
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'todo',
            'priority' => $data['priority'] ?? 'medium',
            'assigned_to' => $data['assigned_to'] ?? null,
            'assigned_by' => $data['assigned_to'] ? $creator->id : null,
            'created_by' => $creator->id,
            'due_date' => isset($data['due_date']) ? \Carbon\Carbon::parse($data['due_date']) : null,
            'start_date' => isset($data['start_date']) ? \Carbon\Carbon::parse($data['start_date']) : null,
            'position' => $maxPosition + 1,
            'tags' => $data['tags'] ?? [],
            'checklist' => $data['checklist'] ?? [],
            'is_pinned' => $data['is_pinned'] ?? false,
            'is_internal_only' => $data['is_internal_only'] ?? false,
        ]);
    }

    /**
     * Update a task.
     * ALL users with project access can update tasks.
     *
     * @param int $taskId
     * @param array $data
     * @param User $user
     * @return Task
     * @throws \Exception
     */
    public function updateTask(int $taskId, array $data, User $user): Task
    {
        $task = Task::findOrFail($taskId);

        // Verify user has access to project
        if (!$this->projectAccessService->canUserAccessProject($user, $task->project_id)) {
            throw new \Exception('You do not have access to this project', 403);
        }

        // Update status to 'done' automatically sets completed_at
        if (isset($data['status']) && $data['status'] === 'done' && $task->status !== 'done') {
            $data['completed_at'] = now();
        } elseif (isset($data['status']) && $data['status'] !== 'done') {
            $data['completed_at'] = null;
        }

        // Update assigned_by if assignment changes
        if (isset($data['assigned_to']) && $data['assigned_to'] !== $task->assigned_to) {
            $data['assigned_by'] = $user->id;
        }

        $task->update($data);

        return $task->fresh();
    }

    /**
     * Delete a task.
     * ALL users with project access can delete tasks.
     *
     * @param int $taskId
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function deleteTask(int $taskId, User $user): bool
    {
        $task = Task::findOrFail($taskId);

        // Verify user has access to project
        if (!$this->projectAccessService->canUserAccessProject($user, $task->project_id)) {
            throw new \Exception('You do not have access to this project', 403);
        }

        return $task->delete();
    }

    /**
     * Get tasks for a project.
     *
     * @param int $projectId
     * @param User $user
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function getProjectTasks(int $projectId, User $user, array $filters = [])
    {
        // Verify user has access to project
        if (!$this->projectAccessService->canUserAccessProject($user, $projectId)) {
            throw new \Exception('You do not have access to this project', 403);
        }

        $query = Task::where('project_id', $projectId)
            ->with(['assignedTo', 'assignedBy', 'creator', 'comments.user']);

        // Filter internal-only tasks for client users
        if (!$user->is_metatech_employee) {
            $query->visibleToClients();
        }

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['is_pinned'])) {
            $query->where('is_pinned', $filters['is_pinned']);
        }

        // Order: pinned first, then by position, then by created_at
        $query->orderBy('is_pinned', 'desc')
              ->orderBy('position', 'asc')
              ->orderBy('created_at', 'desc');

        return $query->get();
    }

    /**
     * Get a single task with details.
     *
     * @param int $taskId
     * @param User $user
     * @return Task
     * @throws \Exception
     */
    public function getTask(int $taskId, User $user): Task
    {
        $task = Task::with(['assignedTo', 'assignedBy', 'creator', 'allComments.user', 'project'])
            ->findOrFail($taskId);

        // Verify user has access to project
        if (!$this->projectAccessService->canUserAccessProject($user, $task->project_id)) {
            throw new \Exception('You do not have access to this project', 403);
        }

        // Check if client user is trying to access internal-only task
        if (!$user->is_metatech_employee && $task->is_internal_only) {
            throw new \Exception('You do not have permission to view this task', 403);
        }

        // Filter internal-only comments for client users
        if (!$user->is_metatech_employee) {
            $task->setRelation('allComments', $task->allComments->filter(function ($comment) {
                return !$comment->is_internal_only;
            }));
        }

        return $task;
    }

    /**
     * Get user's tasks across all projects.
     *
     * @param User $user
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserTasks(User $user, array $filters = [])
    {
        // Get all projects user has access to
        $projects = $this->projectAccessService->getUserProjects($user);
        $projectIds = $projects->pluck('id')->toArray();

        if (empty($projectIds)) {
            return collect([]);
        }

        $query = Task::whereIn('project_id', $projectIds)
            ->with(['project', 'assignedTo', 'assignedBy', 'creator']);

        // Filter internal-only tasks for client users
        if (!$user->is_metatech_employee) {
            $query->visibleToClients();
        }

        // Filter by assigned user
        if (isset($filters['assigned_to_me']) && $filters['assigned_to_me']) {
            $query->where('assigned_to', $user->id);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->orderBy('due_date', 'asc')
                     ->orderBy('created_at', 'desc')
                     ->get();
    }

    /**
     * Add comment to task.
     * ALL users with project access can comment.
     *
     * @param int $taskId
     * @param array $data
     * @param User $user
     * @return TaskComment
     * @throws \Exception
     */
    public function addComment(int $taskId, array $data, User $user): TaskComment
    {
        $task = Task::findOrFail($taskId);

        // Verify user has access to project
        if (!$this->projectAccessService->canUserAccessProject($user, $task->project_id)) {
            throw new \Exception('You do not have access to this project', 403);
        }

        return TaskComment::create([
            'task_id' => $taskId,
            'user_id' => $user->id,
            'comment' => trim($data['comment']),
            'parent_comment_id' => $data['parent_comment_id'] ?? null,
            'mentions' => $data['mentions'] ?? [],
            'attachments' => $data['attachments'] ?? [],
        ]);
    }

    /**
     * Update task position (for drag-and-drop reordering).
     *
     * @param int $taskId
     * @param int $newPosition
     * @param User $user
     * @return Task
     * @throws \Exception
     */
    public function updateTaskPosition(int $taskId, int $newPosition, User $user): Task
    {
        $task = Task::findOrFail($taskId);

        // Verify user has access to project
        if (!$this->projectAccessService->canUserAccessProject($user, $task->project_id)) {
            throw new \Exception('You do not have access to this project', 403);
        }

        $task->position = $newPosition;
        $task->save();

        return $task;
    }

    /**
     * Bulk update task positions (for drag-and-drop reordering).
     *
     * @param array $taskPositions [['id' => 1, 'position' => 0], ...]
     * @param User $user
     * @return void
     * @throws \Exception
     */
    public function bulkUpdateTaskPositions(array $taskPositions, User $user): void
    {
        DB::transaction(function () use ($taskPositions, $user) {
            foreach ($taskPositions as $item) {
                $task = Task::findOrFail($item['id']);
                
                // Verify user has access to project
                if (!$this->projectAccessService->canUserAccessProject($user, $task->project_id)) {
                    throw new \Exception('You do not have access to this project', 403);
                }

                $task->position = $item['position'];
                $task->save();
            }
        });
    }
}

