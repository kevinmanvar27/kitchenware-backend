<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Vendor;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of tasks
     */
    public function index(Request $request)
    {
        $query = Task::with(['assignedBy', 'assignedTo', 'vendor'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by vendor
        if ($request->has('vendor_id') && $request->vendor_id !== '') {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by assigned user
        if ($request->has('assigned_to') && $request->assigned_to !== '') {
            $query->where('assigned_to', $request->assigned_to);
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
        $vendors = Vendor::all();
        $statuses = Task::getStatuses();

        // Return view for web requests, JSON for API
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'tasks' => $tasks,
                'vendors' => $vendors,
                'statuses' => $statuses,
            ]);
        }

        return view('admin.tasks.index', compact('tasks', 'vendors', 'statuses'));
    }

    /**
     * Show the form for creating a new task
     */
    public function create()
    {
        // Get all vendors
        $vendors = Vendor::with('user')->get();
        
        // Get all vendor staff
        $vendorStaff = User::where('user_role', 'vendor_staff')
            ->orWhere('user_role', 'vendor')
            ->get();

        // Return view for web requests, JSON for API
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'vendors' => $vendors,
                'vendorStaff' => $vendorStaff,
            ]);
        }

        return view('admin.tasks.create', compact('vendors', 'vendorStaff'));
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            // Return JSON for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            // Redirect back with errors for web requests
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $attachmentPath = $file->storeAs('tasks', $filename, 'public');
            $attachmentPath = $filename; // Store only filename
        }

        // Create task
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'attachment' => $attachmentPath,
            'assigned_by' => Auth::id(),
            'assigned_to' => $request->assigned_to,
            'vendor_id' => $request->vendor_id,
            'status' => Task::STATUS_PENDING,
        ]);

        // Load relationships
        $task->load(['assignedBy', 'assignedTo', 'vendor']);

        // Send notification to assigned user
        $assignedUser = User::find($request->assigned_to);
        if ($assignedUser && $assignedUser->device_token) {
            $payload = [
                'title' => 'New Task Assigned',
                'body' => "You have been assigned a new task: {$task->title}",
                'message' => "You have been assigned a new task: {$task->title}",
                'type' => 'task_assigned',
                'data' => [
                    'type' => 'task_assigned',
                    'task_id' => (string) $task->id,
                    'task_title' => $task->title,
                    'status' => $task->status,
                ]
            ];
            $this->notificationService->sendToUser($assignedUser, $payload);
        }

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'task' => $task,
            ], 201);
        }

        // Redirect for web requests
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task created successfully and notification sent to assigned user.');
    }

    /**
     * Display the specified task
     */
    public function show($id)
    {
        $task = Task::with(['assignedBy', 'assignedTo', 'vendor', 'comments.user'])
            ->findOrFail($id);

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task,
            ]);
        }

        // Return view for web requests
        return view('admin.tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task
     */
    public function edit($id)
    {
        $task = Task::with(['assignedBy', 'assignedTo', 'vendor'])->findOrFail($id);
        
        // Get all vendors
        $vendors = Vendor::with('user')->get();
        
        // Get all vendor staff
        $vendorStaff = User::where('user_role', 'vendor_staff')
            ->orWhere('user_role', 'vendor')
            ->get();

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task,
                'vendors' => $vendors,
                'vendorStaff' => $vendorStaff,
            ]);
        }

        // Return view for web requests
        return view('admin.tasks.edit', compact('task', 'vendors', 'vendorStaff'));
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        if ($validator->fails()) {
            // Return JSON for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            // Redirect back with errors for web requests
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment
            if ($task->attachment) {
                Storage::disk('public')->delete('tasks/' . $task->attachment);
            }

            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $attachmentPath = $file->storeAs('tasks', $filename, 'public');
            $task->attachment = $filename;
        }

        // Update task
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'vendor_id' => $request->vendor_id,
        ]);

        // Load relationships
        $task->load(['assignedBy', 'assignedTo', 'vendor']);

        // Send notification if assigned user changed
        if ($task->wasChanged('assigned_to')) {
            $assignedUser = User::find($request->assigned_to);
            if ($assignedUser && $assignedUser->device_token) {
                $payload = [
                    'title' => 'Task Reassigned',
                    'body' => "You have been assigned a task: {$task->title}",
                    'message' => "You have been assigned a task: {$task->title}",
                    'type' => 'task_assigned',
                    'data' => [
                        'type' => 'task_assigned',
                        'task_id' => (string) $task->id,
                        'task_title' => $task->title,
                        'status' => $task->status,
                    ]
                ];
                $this->notificationService->sendToUser($assignedUser, $payload);
            }
        }

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'task' => $task,
            ]);
        }

        // Redirect for web requests
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified task
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        // Delete attachment if exists
        if ($task->attachment) {
            Storage::disk('public')->delete('tasks/' . $task->attachment);
        }

        $task->delete();

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);
        }

        // Redirect for web requests
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * Add a comment to the task
     */
    public function addComment(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // Prevent comments on verified tasks
        if ($task->status === Task::STATUS_VERIFIED) {
            // Return JSON for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add comment. This task is verified and locked.',
                ], 403);
            }
            
            // Redirect back with error for web requests
            return redirect()->back()
                ->with('error', 'Cannot add comment. This task is verified and locked.');
        }

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            // Return JSON for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            // Redirect back with errors for web requests
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            'is_internal' => $request->has('is_internal') ? (bool)$request->is_internal : false,
        ]);

        $comment->load('user');

        // Send notification to assigned user
        $assignedUser = $task->assignedTo;
        if ($assignedUser && $assignedUser->device_token && $assignedUser->id !== Auth::id()) {
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
            $this->notificationService->sendToUser($assignedUser, $payload);
        }

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'comment' => $comment,
            ], 201);
        }

        // Redirect for web requests
        return redirect()->route('admin.tasks.show', $task->id)
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // Prevent status changes on verified tasks
        if ($task->status === Task::STATUS_VERIFIED) {
            // Return JSON for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update status. This task is verified and locked.',
                ], 403);
            }
            
            // Redirect back with error for web requests
            return redirect()->back()
                ->with('error', 'Cannot update status. This task is verified and locked.');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,question,done,verified',
        ]);

        if ($validator->fails()) {
            // Return JSON for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            // Redirect back with errors for web requests
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldStatus = $task->status;
        $task->status = $request->status;
        $task->save();

        // Send notification based on status change
        if ($request->status === Task::STATUS_DONE && $oldStatus !== Task::STATUS_DONE) {
            // Notify admin that task is done
            $admin = $task->assignedBy;
            if ($admin && $admin->device_token) {
                $payload = [
                    'title' => 'Task Completed',
                    'body' => "Task '{$task->title}' has been marked as done",
                    'message' => "Task '{$task->title}' has been marked as done",
                    'type' => 'task_done',
                    'data' => [
                        'type' => 'task_done',
                        'task_id' => (string) $task->id,
                        'task_title' => $task->title,
                    ]
                ];
                $this->notificationService->sendToUser($admin, $payload);
            }
        } elseif ($request->status === Task::STATUS_QUESTION && $oldStatus !== Task::STATUS_QUESTION) {
            // Notify admin that there's a question
            $admin = $task->assignedBy;
            if ($admin && $admin->device_token) {
                $payload = [
                    'title' => 'Task Question',
                    'body' => "Question raised on task: {$task->title}",
                    'message' => "Question raised on task: {$task->title}",
                    'type' => 'task_question',
                    'data' => [
                        'type' => 'task_question',
                        'task_id' => (string) $task->id,
                        'task_title' => $task->title,
                    ]
                ];
                $this->notificationService->sendToUser($admin, $payload);
            }
        }

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully',
                'task' => $task->load(['assignedBy', 'assignedTo', 'vendor']),
            ]);
        }

        // Redirect for web requests
        return redirect()->route('admin.tasks.show', $task->id)
            ->with('success', 'Task status updated successfully.');
    }

    /**
     * Get task statistics
     */
    public function statistics()
    {
        $stats = [
            'total' => Task::count(),
            'pending' => Task::pending()->count(),
            'in_progress' => Task::inProgress()->count(),
            'with_questions' => Task::withQuestions()->count(),
            'done' => Task::done()->count(),
            'verified' => Task::verified()->count(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }
}
