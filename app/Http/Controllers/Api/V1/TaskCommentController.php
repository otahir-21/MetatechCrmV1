<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskCommentCreateRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Add comment to task.
     * ALL users with project access can comment.
     *
     * @param TaskCommentCreateRequest $request
     * @param int $taskId
     * @return JsonResponse
     */
    public function store(TaskCommentCreateRequest $request, int $taskId): JsonResponse
    {
        try {
            $user = auth()->user();
            $comment = $this->taskService->addComment($taskId, $request->validated(), $user);

            return response()->json([
                'message' => 'Comment added successfully',
                'data' => [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'email' => $comment->user->email,
                    ],
                    'parent_comment_id' => $comment->parent_comment_id,
                    'mentions' => $comment->mentions ?? [],
                    'attachments' => $comment->attachments ?? [],
                    'created_at' => $comment->created_at->toIso8601String(),
                ],
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
     * Get comments for a task.
     *
     * @param int $taskId
     * @return JsonResponse
     */
    public function index(int $taskId): JsonResponse
    {
        try {
            $user = auth()->user();
            $task = $this->taskService->getTask($taskId, $user);

            $comments = $task->allComments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'email' => $comment->user->email,
                    ],
                    'parent_comment_id' => $comment->parent_comment_id,
                    'mentions' => $comment->mentions ?? [],
                    'attachments' => $comment->attachments ?? [],
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'comment' => $reply->comment,
                            'user' => [
                                'id' => $reply->user->id,
                                'name' => $reply->user->name,
                                'email' => $reply->user->email,
                            ],
                            'created_at' => $reply->created_at->toIso8601String(),
                        ];
                    }),
                    'created_at' => $comment->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'data' => $comments,
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
}
