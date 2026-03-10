<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of ALL activity logs (admin + vendor).
     * Super admin can see everything.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get unique actions for filter dropdown
        $actions = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Get ALL users for filter dropdown (admin + vendor users)
        $users = User::whereIn('user_role', ['super_admin', 'admin', 'editor', 'staff', 'vendor', 'vendor_staff'])
            ->orderBy('name')
            ->get(['id', 'name', 'user_role']);

        // Get all vendors for filter dropdown
        $vendors = Vendor::orderBy('store_name')->get(['id', 'store_name']);

        // Get unique model types for filter dropdown
        $modelTypes = ActivityLog::whereNotNull('model_type')
            ->select('model_type')
            ->distinct()
            ->pluck('model_type')
            ->map(function ($type) {
                $parts = explode('\\', $type);
                return [
                    'full' => $type,
                    'name' => end($parts),
                ];
            });

        return view('admin.activity-logs.index', compact('actions', 'users', 'vendors', 'modelTypes'));
    }

    /**
     * Get activity logs data for DataTable.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        $query = ActivityLog::with(['user', 'vendor']);

        // Filter by log type (admin/vendor)
        if ($request->filled('log_type')) {
            $query->where('log_type', $request->log_type);
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get total count before search
        $totalRecords = $query->count();

        // DataTable search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('description', 'like', '%' . $searchValue . '%')
                  ->orWhere('action', 'like', '%' . $searchValue . '%')
                  ->orWhere('model_type', 'like', '%' . $searchValue . '%')
                  ->orWhereHas('user', function ($userQuery) use ($searchValue) {
                      $userQuery->where('name', 'like', '%' . $searchValue . '%');
                  })
                  ->orWhereHas('vendor', function ($vendorQuery) use ($searchValue) {
                      $vendorQuery->where('store_name', 'like', '%' . $searchValue . '%');
                  });
            });
        }

        $filteredRecords = $query->count();

        // Sorting
        $orderColumnIndex = $request->input('order.0.column', 7); // Default to date column
        $orderDirection = $request->input('order.0.dir', 'desc');
        
        $columns = ['id', 'log_type', 'user_id', 'vendor_id', 'action', 'description', 'model_type', 'created_at'];
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        
        $query->orderBy($orderColumn, $orderDirection);

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);
        
        $logs = $query->skip($start)->take($length)->get();

        // Format data for DataTable
        $data = $logs->map(function ($log, $index) use ($start) {
            $userHtml = '';
            if ($log->user) {
                $userHtml = '
                    <div class="d-flex align-items-center">
                        <img src="' . e($log->user->avatar_url) . '" class="rounded-circle me-2" width="32" height="32" alt="' . e($log->user->name) . '">
                        <div>
                            <div class="fw-medium small">' . e($log->user->name) . '</div>
                            <small class="text-muted">' . e($log->user->user_role) . '</small>
                        </div>
                    </div>';
            } else {
                $userHtml = '
                    <div class="d-flex align-items-center">
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                            <i class="fas fa-robot text-white small"></i>
                        </div>
                        <span class="text-muted">System</span>
                    </div>';
            }

            $logTypeBadge = $log->log_type == 'admin' 
                ? '<span class="badge bg-primary rounded-pill">Admin Panel</span>'
                : '<span class="badge bg-info rounded-pill">Vendor Panel</span>';

            $vendorHtml = $log->vendor 
                ? '<span class="badge bg-light text-dark border">' . e($log->vendor->store_name) . '</span>'
                : '<span class="text-muted">-</span>';

            $actionBadge = '<span class="badge bg-' . e($log->action_color) . '-subtle text-' . e($log->action_color) . '-emphasis rounded-pill px-2 py-1">
                <i class="fas ' . e($log->action_icon) . ' me-1"></i>' . ucfirst(e($log->action)) . '</span>';

            $modelHtml = '';
            if ($log->model_name) {
                $modelHtml = '<span class="badge bg-light text-dark border">' . e($log->model_name) . '</span>';
                if ($log->model_id) {
                    $modelHtml .= ' <small class="text-muted">#' . e($log->model_id) . '</small>';
                }
            } else {
                $modelHtml = '<span class="text-muted">-</span>';
            }

            $dateHtml = '<div class="small">' . $log->created_at->format('M d, Y') . '</div>
                <small class="text-muted">' . $log->created_at->format('h:i A') . '</small>';

            $actionsHtml = '<button type="button" class="btn btn-sm btn-outline-primary rounded-pill view-log-btn" 
                data-log-id="' . $log->id . '" data-bs-toggle="modal" data-bs-target="#logDetailModal">
                <i class="fas fa-eye"></i></button>';

            return [
                'DT_RowIndex' => $start + $index + 1,
                'log_type' => $logTypeBadge,
                'user' => $userHtml,
                'vendor' => $vendorHtml,
                'action' => $actionBadge,
                'description' => '<span class="text-truncate d-inline-block" style="max-width: 250px;" title="' . e($log->description) . '">' . e($log->description) . '</span>',
                'model' => $modelHtml,
                'date' => $dateHtml,
                'actions' => $actionsHtml,
                'created_at_raw' => $log->created_at->timestamp,
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    /**
     * Display the specified activity log.
     *
     * @param ActivityLog $activityLog
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ActivityLog $activityLog)
    {
        $activityLog->load(['user', 'vendor']);
        
        return response()->json([
            'id' => $activityLog->id,
            'log_type' => $activityLog->log_type,
            'user' => $activityLog->user ? $activityLog->user->name : 'System',
            'user_role' => $activityLog->user ? $activityLog->user->user_role : null,
            'vendor' => $activityLog->vendor ? $activityLog->vendor->store_name : null,
            'action' => $activityLog->action,
            'action_color' => $activityLog->action_color,
            'action_icon' => $activityLog->action_icon,
            'description' => $activityLog->description,
            'model_name' => $activityLog->model_name,
            'model_id' => $activityLog->model_id,
            'old_values' => $activityLog->old_values,
            'new_values' => $activityLog->new_values,
            'ip_address' => $activityLog->ip_address,
            'user_agent' => $activityLog->user_agent,
            'created_at' => $activityLog->created_at->format('M d, Y h:i A'),
            'created_at_diff' => $activityLog->created_at->diffForHumans(),
        ]);
    }

    /**
     * Clear old activity logs.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'log_type' => 'nullable|in:admin,vendor,all',
        ]);

        $days = $request->days;
        $logType = $request->log_type ?? 'all';
        $cutoffDate = now()->subDays($days);

        $query = ActivityLog::where('created_at', '<', $cutoffDate);
        
        if ($logType !== 'all') {
            $query->where('log_type', $logType);
        }

        $deleted = $query->delete();

        $typeLabel = $logType === 'all' ? 'all' : $logType;
        return redirect()->route('admin.activity-logs.index')
            ->with('success', "Successfully deleted {$deleted} {$typeLabel} activity logs older than {$days} days.");
    }

    /**
     * Export activity logs to CSV.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with(['user', 'vendor'])->latest();

        // Apply same filters as index
        if ($request->filled('log_type')) {
            $query->where('log_type', $request->log_type);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['ID', 'Type', 'User', 'Role', 'Vendor', 'Action', 'Description', 'Model', 'Model ID', 'IP Address', 'Date']);
            
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    ucfirst($log->log_type),
                    $log->user ? $log->user->name : 'System',
                    $log->user ? $log->user->user_role : '',
                    $log->vendor ? $log->vendor->store_name : '',
                    $log->action,
                    $log->description,
                    $log->model_name,
                    $log->model_id,
                    $log->ip_address,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
