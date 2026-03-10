<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of tasks assigned to the logged-in vendor/staff
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Task::with(['assignedBy', 'assignedTo', 'vendor'])
            ->where('assigned_to', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->paginate(15);
        $statuses = Task::getStatuses();

        // Get statistics for the vendor
        $stats = [
            'total' => Task::where('assigned_to', $user->id)->count(),
            'pending' => Task::where('assigned_to', $user->id)->pending()->count(),
            'in_progress' => Task::where('assigned_to', $user->id)->inProgress()->count(),
            'with_questions' => Task::where('assigned_to', $user->id)->withQuestions()->count(),
            'done' => Task::where('assigned_to', $user->id)->done()->count(),
            'verified' => Task::where('assigned_to', $user->id)->verified()->count(),
        ];

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'tasks' => $tasks,
                'statuses' => $statuses,
                'statistics' => $stats,
            ]);
        }

        // Return view for web requests
        return view('vendor.tasks.index', compact('tasks', 'statuses', 'stats'));
    }

    /**
     * Display the specified task
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $task = Task::with(['assignedBy', 'assignedTo', 'vendor', 'comments.user'])
            ->where('assigned_to', $user->id)
            ->findOrFail($id);

        // Return JSON for API requests
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'task' => $task,
            ]);
        }

        // Return view for web requests
        return view('vendor.tasks.show', compact('task'));
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        
        $task = Task::where('assigned_to', $user->id)->findOrFail($id);

        // Prevent changes to verified tasks
        if ($task->status === Task::STATUS_VERIFIED) {
            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update status. This task is verified and locked.',
                ], 403);
            }

            // Redirect for web requests
            return redirect()->route('vendor.tasks.show', $task->id)
                ->with('error', 'Cannot update status. This task is verified and locked.');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:in_progress,question,done',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Vendor staff can only update to certain statuses
        $allowedStatuses = [Task::STATUS_IN_PROGRESS, Task::STATUS_QUESTION, Task::STATUS_DONE];
        if (!in_array($request->status, $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition',
            ], 403);
        }

        $oldStatus = $task->status;
        $task->status = $request->status;
        $task->save();

        // Send notification to admin based on status change
        $admin = $task->assignedBy;
        if ($admin && $admin->device_token) {
            $notificationData = match($request->status) {
                Task::STATUS_IN_PROGRESS => [
                    'title' => 'Task In Progress',
                    'body' => "Task '{$task->title}' is now in progress",
                    'type' => 'task_in_progress',
                ],
                Task::STATUS_QUESTION => [
                    'title' => 'Task Question',
                    'body' => "Question raised on task: {$task->title}",
                    'type' => 'task_question',
                ],
                Task::STATUS_DONE => [
                    'title' => 'Task Completed',
                    'body' => "Task '{$task->title}' has been marked as done",
                    'type' => 'task_done',
                ],
                default => null,
            };

            if ($notificationData && $oldStatus !== $request->status) {
                $payload = [
                    'title' => $notificationData['title'],
                    'body' => $notificationData['body'],
                    'message' => $notificationData['body'],
                    'type' => $notificationData['type'],
                    'data' => [
                        'type' => $notificationData['type'],
                        'task_id' => (string) $task->id,
                        'task_title' => $task->title,
                        'status' => $task->status,
                    ]
                ];
                $this->notificationService->sendToUser($admin, $payload);
            }
        }

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully',
                'task' => $task->load(['assignedBy', 'assignedTo', 'vendor']),
            ]);
        }

        // Redirect for web requests
        return redirect()->route('vendor.tasks.show', $task->id)
            ->with('success', 'Task status updated successfully');
    }

    /**
     * Add a comment to the task
     */
    public function addComment(Request $request, $id)
    {
        $user = Auth::user();
        
        $task = Task::where('assigned_to', $user->id)->findOrFail($id);

        // Prevent comments on verified tasks
        if ($task->status === Task::STATUS_VERIFIED) {
            // Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add comment. This task is verified and locked.',
                ], 403);
            }

            // Redirect for web requests
            return redirect()->route('vendor.tasks.show', $task->id)
                ->with('error', 'Cannot add comment. This task is verified and locked.');
        }

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
        ]);

        $comment->load('user');

        // Send notification to admin
        $admin = $task->assignedBy;
        if ($admin && $admin->device_token) {
            $payload = [
                'title' => 'New Comment on Task',
                'body' => "New comment on task: {$task->title}",
                'message' => "New comment on task: {$task->title}",
                'type' => 'task_comment',
                'data' => [
                    'type' => 'task_comment',
                    'task_id' => (string) $task->id,
                    'task_title' => $task->title,
                    'comment' => $request->comment,
                ]
            ];
            $this->notificationService->sendToUser($admin, $payload);
        }

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'comment' => $comment,
            ], 201);
        }

        // Redirect for web requests
        return redirect()->route('vendor.tasks.show', $task->id)
            ->with('success', 'Comment added successfully');
    }

    /**
     * Verify a completed task (vendor owner only)
     */
    public function verify($id)
    {
        $user = Auth::user();
        
        // Only vendor owners can verify tasks
        if (!$user->isVendor()) {
            return response()->json([
                'success' => false,
                'message' => 'Only vendor owners can verify tasks',
            ], 403);
        }

        $task = Task::where('assigned_to', $user->id)
            ->where('status', Task::STATUS_DONE)
            ->findOrFail($id);

        $task->status = Task::STATUS_VERIFIED;
        $task->save();

        // Send notification to admin
        $admin = $task->assignedBy;
        if ($admin && $admin->device_token) {
            $payload = [
                'title' => 'Task Verified',
                'body' => "Task '{$task->title}' has been verified",
                'message' => "Task '{$task->title}' has been verified",
                'type' => 'task_verified',
                'data' => [
                    'type' => 'task_verified',
                    'task_id' => (string) $task->id,
                    'task_title' => $task->title,
                ]
            ];
            $this->notificationService->sendToUser($admin, $payload);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task verified successfully',
            'task' => $task->load(['assignedBy', 'assignedTo', 'vendor']),
        ]);
    }

    /**
     * Get task statistics for the vendor
     */
    public function statistics()
    {
        $user = Auth::user();
        
        $stats = [
            'total' => Task::where('assigned_to', $user->id)->count(),
            'pending' => Task::where('assigned_to', $user->id)->pending()->count(),
            'in_progress' => Task::where('assigned_to', $user->id)->inProgress()->count(),
            'with_questions' => Task::where('assigned_to', $user->id)->withQuestions()->count(),
            'done' => Task::where('assigned_to', $user->id)->done()->count(),
            'verified' => Task::where('assigned_to', $user->id)->verified()->count(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }
}
