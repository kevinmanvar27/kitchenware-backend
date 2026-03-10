<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Get the current vendor ID.
     *
     * @return int|null
     */
    private function getVendorId()
    {
        $user = Auth::user();
        
        if ($user->isVendor() && $user->vendor) {
            return $user->vendor->id;
        }
        
        if ($user->isVendorStaff() && $user->vendorStaff) {
            return $user->vendorStaff->vendor_id;
        }
        
        return null;
    }

    /**
     * Display a listing of activity logs for the vendor.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $vendorId = $this->getVendorId();
        
        if (!$vendorId) {
            abort(403, 'Vendor not found.');
        }

        // Get unique actions for filter dropdown (only for this vendor)
        $actions = ActivityLog::query()
            ->vendor()
            ->forVendor($vendorId)
            ->select('action')
            ->distinct()
            ->pluck('action');

        // Get users (vendor owner + staff) for filter dropdown
        $user = Auth::user();
        $vendor = $user->isVendor() ? $user->vendor : $user->vendorStaff->vendor;
        
        $userIds = [$vendor->user_id];
        $staffUserIds = $vendor->staff()->pluck('user_id')->toArray();
        $userIds = array_merge($userIds, $staffUserIds);
        
        $users = \App\Models\User::whereIn('id', $userIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get unique model types for filter dropdown
        $modelTypes = ActivityLog::query()
            ->vendor()
            ->forVendor($vendorId)
            ->whereNotNull('model_type')
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

        return view('vendor.activity-logs.index', compact('actions', 'users', 'modelTypes'));
    }

    /**
     * Get activity logs data for DataTable.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        $vendorId = $this->getVendorId();
        
        if (!$vendorId) {
            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $query = ActivityLog::query()
            ->vendor()
            ->forVendor($vendorId)
            ->with('user');

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user (staff member)
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
                  });
            });
        }

        $filteredRecords = $query->count();

        // Sorting
        $orderColumnIndex = $request->input('order.0.column', 5); // Default to date column
        $orderDirection = $request->input('order.0.dir', 'desc');
        
        $columns = ['id', 'user_id', 'action', 'description', 'model_type', 'created_at'];
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
                $roleLabel = '';
                if ($log->user->isVendor()) {
                    $roleLabel = 'Owner';
                } elseif ($log->user->isVendorStaff()) {
                    $roleLabel = 'Staff';
                }
                
                $userHtml = '
                    <div class="d-flex align-items-center">
                        <img src="' . e($log->user->avatar_url) . '" class="rounded-circle me-2" width="32" height="32" alt="' . e($log->user->name) . '">
                        <div>
                            <div class="fw-medium small">' . e($log->user->name) . '</div>
                            <small class="text-muted">' . e($roleLabel) . '</small>
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
                'user' => $userHtml,
                'action' => $actionBadge,
                'description' => '<span class="text-truncate d-inline-block" style="max-width: 300px;" title="' . e($log->description) . '">' . e($log->description) . '</span>',
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
        $vendorId = $this->getVendorId();
        
        // Ensure the log belongs to this vendor
        if ($activityLog->vendor_id !== $vendorId) {
            abort(403, 'Unauthorized access.');
        }

        $activityLog->load('user');
        
        return response()->json([
            'id' => $activityLog->id,
            'user' => $activityLog->user ? $activityLog->user->name : 'System',
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
     * Export activity logs to CSV.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $vendorId = $this->getVendorId();
        
        if (!$vendorId) {
            abort(403, 'Vendor not found.');
        }

        $query = ActivityLog::query()
            ->vendor()
            ->forVendor($vendorId)
            ->with('user')
            ->latest();

        // Apply same filters as index
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

        $filename = 'vendor_activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['ID', 'User', 'Action', 'Description', 'Model', 'Model ID', 'IP Address', 'Date']);
            
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->name : 'System',
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
