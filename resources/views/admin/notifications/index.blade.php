@extends('admin.layouts.app')

@section('title', 'Notifications - ' . config('app.name', 'Laravel'))

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Notifications',
                'breadcrumbs' => [
                    'Notifications' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Notifications</h4>
                                        <p class="mb-0 text-muted small">Manage your notifications</p>
                                    </div>
                                    <button id="markAllAsRead" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4">
                                        <i class="fas fa-check-double me-1 me-md-2"></i><span class="d-none d-sm-inline">Mark All as Read</span><span class="d-sm-none">Mark All</span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="notificationsDataTable">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Message</th>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="notificationsTable">
                                            @forelse(auth()->user()->notifications as $notification)
                                                <tr data-notification-id="{{ $notification->id }}" class="{{ $notification->read ? 'text-muted' : 'table-active' }}">
                                                    <td>{{ $notification->title }}</td>
                                                    <td>{{ $notification->message }}</td>
                                                    <td>
                                                        @if($notification->type === 'proforma_invoice')
                                                            <span class="badge bg-primary">Invoice</span>
                                                        @elseif($notification->type === 'user_registered')
                                                            <span class="badge bg-success">User</span>
                                                        @elseif($notification->type === 'settings_updated')
                                                            <span class="badge bg-warning">Settings</span>
                                                        @else
                                                            <span class="badge bg-info">General</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $notification->created_at->format('M d, Y H:i') }}</td>
                                                    <td>
                                                        @if($notification->read)
                                                            <span class="badge bg-secondary">Read</span>
                                                        @else
                                                            <span class="badge bg-danger">Unread</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!$notification->read)
                                                            <button class="btn btn-sm btn-outline-primary mark-as-read" data-notification-id="{{ $notification->id }}">
                                                                Mark as Read
                                                            </button>
                                                        @else
                                                            <span>-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No notifications found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    const notificationsTable = $('#notificationsDataTable').DataTable({
        "order": [[3, "desc"]], // Sort by Date (descending)
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "language": {
            "search": "Search notifications:",
            "lengthMenu": "Show _MENU_ notifications per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ notifications",
            "infoEmpty": "No notifications available",
            "infoFiltered": "(filtered from _MAX_ total notifications)",
            "zeroRecords": "No matching notifications found",
            "emptyTable": "No notifications available"
        },
        "columnDefs": [
            { "orderable": false, "targets": [5] } // Disable sorting on Actions column
        ],
        "responsive": true,
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
    });

    const markAllAsReadBtn = document.getElementById('markAllAsRead');
    const markAsReadButtons = document.querySelectorAll('.mark-as-read');
    
    // Mark individual notifications as read
    markAsReadButtons.forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.getAttribute('data-notification-id');
            const row = this.closest('tr');
            
            fetch(`/admin/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to reflect changes in DataTable
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    
    // Mark all notifications as read
    markAllAsReadBtn.addEventListener('click', function() {
        fetch('/admin/notifications/mark-all-as-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to reflect changes in DataTable
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});
</script>
@endsection