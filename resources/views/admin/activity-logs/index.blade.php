@extends('admin.layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Activity Logs'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Activity Logs</h4>
                                        <p class="mb-0 text-muted small">Track all admin and vendor panel activities</p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.activity-logs.export', request()->query()) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                            <i class="fas fa-download me-1"></i> Export CSV
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                                            <i class="fas fa-trash me-1"></i> Clear Old Logs
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <!-- Filters -->
                                <div class="card bg-light border-0 mb-4">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold">Log Type</label>
                                                <select id="filter_log_type" class="form-select form-select-sm">
                                                    <option value="">All Types</option>
                                                    <option value="admin">Admin Panel</option>
                                                    <option value="vendor">Vendor Panel</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold">Vendor</label>
                                                <select id="filter_vendor_id" class="form-select form-select-sm">
                                                    <option value="">All Vendors</option>
                                                    @foreach($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}">{{ $vendor->store_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold">Action</label>
                                                <select id="filter_action" class="form-select form-select-sm">
                                                    <option value="">All Actions</option>
                                                    @foreach($actions as $action)
                                                        <option value="{{ $action }}">{{ ucfirst($action) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold">User</label>
                                                <select id="filter_user_id" class="form-select form-select-sm">
                                                    <option value="">All Users</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold">From Date</label>
                                                <input type="date" id="filter_date_from" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold">To Date</label>
                                                <input type="date" id="filter_date_to" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                        <div class="row g-3 mt-1">
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold">Model</label>
                                                <select id="filter_model_type" class="form-select form-select-sm">
                                                    <option value="">All Models</option>
                                                    @foreach($modelTypes as $type)
                                                        <option value="{{ $type['name'] }}">{{ $type['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end gap-2">
                                                <button type="button" id="applyFilters" class="btn btn-sm btn-theme">
                                                    <i class="fas fa-filter me-1"></i> Filter
                                                </button>
                                                <button type="button" id="resetFilters" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Activity Logs Table -->
                                <div class="table-responsive">
                                    <table id="activityLogsTable" class="table table-hover align-middle" style="width: 100%;">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="4%">#</th>
                                                <th width="8%">Type</th>
                                                <th width="12%">User</th>
                                                <th width="10%">Vendor</th>
                                                <th width="8%">Action</th>
                                                <th width="28%">Description</th>
                                                <th width="10%">Model</th>
                                                <th width="12%">Date</th>
                                                <th width="8%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data loaded via DataTables AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Log Detail Modal -->
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Activity Log Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="logDetailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Clear Logs Modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-labelledby="clearLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.activity-logs.clear') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="clearLogsModalLabel">
                        <i class="fas fa-trash me-2 text-danger"></i>Clear Old Activity Logs
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">This will permanently delete activity logs older than the specified number of days.</p>
                    <div class="mb-3">
                        <label for="log_type" class="form-label">Log Type to Clear</label>
                        <select name="log_type" id="log_type" class="form-select">
                            <option value="all">All Logs (Admin + Vendor)</option>
                            <option value="admin">Admin Logs Only</option>
                            <option value="vendor">Vendor Logs Only</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="days" class="form-label">Delete logs older than (days)</label>
                        <select name="days" id="days" class="form-select" required>
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90" selected>90 days</option>
                            <option value="180">180 days</option>
                            <option value="365">365 days</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Logs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#activityLogsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.activity-logs.data') }}",
            data: function(d) {
                d.log_type = $('#filter_log_type').val();
                d.vendor_id = $('#filter_vendor_id').val();
                d.action = $('#filter_action').val();
                d.user_id = $('#filter_user_id').val();
                d.model_type = $('#filter_model_type').val();
                d.date_from = $('#filter_date_from').val();
                d.date_to = $('#filter_date_to').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'log_type', name: 'log_type' },
            { data: 'user', name: 'user', orderable: false },
            { data: 'vendor', name: 'vendor', orderable: false },
            { data: 'action', name: 'action' },
            { data: 'description', name: 'description' },
            { data: 'model', name: 'model_type' },
            { data: 'date', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[7, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries per page",
            info: "Showing _START_ to _END_ of _TOTAL_ activity logs",
            infoEmpty: "Showing 0 to 0 of 0 activity logs",
            infoFiltered: "(filtered from _MAX_ total logs)",
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading...',
            emptyTable: '<div class="text-center py-4"><i class="fas fa-history fa-3x mb-3 opacity-50"></i><p class="mb-0">No activity logs found</p></div>',
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        drawCallback: function() {
            // Re-bind click events after each draw
            bindViewLogButtons();
        }
    });

    // Adjust select width after DataTable initializes
    $('.dataTables_length select').css('width', '80px');

    // Apply filters button
    $('#applyFilters').on('click', function() {
        table.ajax.reload();
    });

    // Reset filters button
    $('#resetFilters').on('click', function() {
        $('#filter_log_type').val('');
        $('#filter_vendor_id').val('');
        $('#filter_action').val('');
        $('#filter_user_id').val('');
        $('#filter_model_type').val('');
        $('#filter_date_from').val('');
        $('#filter_date_to').val('');
        table.ajax.reload();
    });

    // Function to bind view log button click events
    function bindViewLogButtons() {
        $(document).off('click', '.view-log-btn').on('click', '.view-log-btn', function() {
            const logId = $(this).data('log-id');
            const contentDiv = $('#logDetailContent');
            
            // Show loading
            contentDiv.html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
            
            // Fetch log details
            fetch(`{{ url('admin/activity-logs') }}/${logId}`)
                .then(response => response.json())
                .then(data => {
                    let changesHtml = '';
                    
                    if (data.old_values || data.new_values) {
                        changesHtml = `
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-exchange-alt me-2"></i>Changes</h6>
                                    <div class="row">
                        `;
                        
                        if (data.old_values && Object.keys(data.old_values).length > 0) {
                            changesHtml += `
                                <div class="col-md-6">
                                    <div class="card bg-danger-subtle border-0">
                                        <div class="card-header bg-transparent border-0 py-2">
                                            <small class="fw-bold text-danger">Old Values</small>
                                        </div>
                                        <div class="card-body py-2">
                                            <pre class="mb-0 small" style="max-height: 200px; overflow: auto;">${JSON.stringify(data.old_values, null, 2)}</pre>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        
                        if (data.new_values && Object.keys(data.new_values).length > 0) {
                            changesHtml += `
                                <div class="col-md-6">
                                    <div class="card bg-success-subtle border-0">
                                        <div class="card-header bg-transparent border-0 py-2">
                                            <small class="fw-bold text-success">New Values</small>
                                        </div>
                                        <div class="card-body py-2">
                                            <pre class="mb-0 small" style="max-height: 200px; overflow: auto;">${JSON.stringify(data.new_values, null, 2)}</pre>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        
                        changesHtml += `
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Build log type badge
                    const logTypeBadge = data.log_type === 'admin' 
                        ? '<span class="badge bg-primary rounded-pill">Admin Panel</span>'
                        : '<span class="badge bg-info rounded-pill">Vendor Panel</span>';
                    
                    contentDiv.html(`
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">Log Type</label>
                                    <p class="mb-0">${logTypeBadge}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">Vendor</label>
                                    <p class="mb-0">${data.vendor || '<span class="text-muted">-</span>'}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">User</label>
                                    <p class="mb-0 fw-medium">${data.user} ${data.user_role ? '<small class="text-muted">(' + data.user_role + ')</small>' : ''}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">Action</label>
                                    <p class="mb-0">
                                        <span class="badge bg-${data.action_color}-subtle text-${data.action_color}-emphasis rounded-pill px-3 py-2">
                                            <i class="fas ${data.action_icon} me-1"></i>
                                            ${data.action.charAt(0).toUpperCase() + data.action.slice(1)}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">Description</label>
                                    <p class="mb-0">${data.description}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">Model</label>
                                    <p class="mb-0">${data.model_name || '-'} ${data.model_id ? '#' + data.model_id : ''}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">IP Address</label>
                                    <p class="mb-0"><code>${data.ip_address || '-'}</code></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">Date & Time</label>
                                    <p class="mb-0">${data.created_at}<br><small class="text-muted">${data.created_at_diff}</small></p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label small text-muted fw-semibold">User Agent</label>
                                    <p class="mb-0 small text-muted">${data.user_agent || '-'}</p>
                                </div>
                            </div>
                        </div>
                        ${changesHtml}
                    `);
                })
                .catch(error => {
                    contentDiv.html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Failed to load activity log details.
                        </div>
                    `);
                });
        });
    }

    // Initial binding
    bindViewLogButtons();
});
</script>
@endsection
