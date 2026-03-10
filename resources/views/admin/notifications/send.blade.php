@extends('admin.layouts.app')


@section('title', 'Send Notifications - ' . config('app.name', 'Laravel'))

@section('content')
{{-- Inline script for toggle functions - must load before forms --}}
<script>
// Toggle schedule options - defined inline so it's available when radio buttons render
function toggleScheduleOptions(prefix) {
    var radioBtn = document.querySelector('input[name="' + prefix + '_schedule_type"]:checked');
    if (!radioBtn) return;
    
    var scheduleType = radioBtn.value;
    var optionsDiv = document.getElementById(prefix + '_schedule_options');
    var scheduledAtInput = document.getElementById(prefix + '_scheduled_at');
    var btn = document.getElementById('sendTo' + prefix.charAt(0).toUpperCase() + prefix.slice(1) + 'Btn');
    
    if (!optionsDiv) return;
    
    if (scheduleType === 'scheduled') {
        // Show date-time field
        optionsDiv.style.display = 'block';
        if (scheduledAtInput) scheduledAtInput.required = true;
        if (btn) {
            btn.innerHTML = '<i class="fas fa-clock me-2"></i><span class="btn-text">Schedule Notification</span>';
        }
    } else {
        // Hide date-time field
        optionsDiv.style.display = 'none';
        if (scheduledAtInput) {
            scheduledAtInput.required = false;
            scheduledAtInput.value = '';
        }
        if (btn) {
            var defaultText = prefix === 'all' ? 'Send to All Users' : 'Send Notification';
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i><span class="btn-text">' + defaultText + '</span>';
        }
    }
}

function setMinDateTime() {
    var now = new Date();
    now.setMinutes(now.getMinutes() + 1); // Allow scheduling 1 minute from now
    
    // Format as local datetime (YYYY-MM-DDTHH:MM) for datetime-local input
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var minDateTime = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    
    var inputs = ['user_scheduled_at', 'group_scheduled_at', 'all_scheduled_at'];
    inputs.forEach(function(id) {
        var input = document.getElementById(id);
        if (input) input.min = minDateTime;
    });
}
</script>

<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Send Notifications',
                'breadcrumbs' => [
                    'Notifications' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Send Push Notifications</h4>
                                    <p class="mb-0 text-muted">Send notifications to users or user groups</p>
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
                                
                                <ul class="nav nav-tabs mb-4" id="notificationTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active rounded-pill px-4" id="user-tab" data-bs-toggle="tab" data-bs-target="#user" type="button" role="tab" aria-controls="user" aria-selected="true">
                                            <i class="fas fa-user me-2"></i>Send to User
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link rounded-pill px-4" id="group-tab" data-bs-toggle="tab" data-bs-target="#group" type="button" role="tab" aria-controls="group" aria-selected="false">
                                            <i class="fas fa-users me-2"></i>Send to Group
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link rounded-pill px-4" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="false">
                                            <i class="fas fa-globe me-2"></i>Send to All
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link rounded-pill px-4" id="token-tab" data-bs-toggle="tab" data-bs-target="#token" type="button" role="tab" aria-controls="token" aria-selected="false">
                                            <i class="fas fa-mobile-alt me-2"></i>Send to Device Token
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="notificationTabContent">
                                    <!-- Send to User Tab -->
                                    <div class="tab-pane fade show active" id="user" role="tabpanel" aria-labelledby="user-tab">
                                        <form id="sendToUserForm">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="user_id" class="form-label fw-medium">Select User</label>
                                                <select class="form-select rounded-pill" id="user_id" name="user_id" required>
                                                    <option value="">Choose a user</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="user_title" class="form-label fw-medium">Title</label>
                                                <input type="text" class="form-control rounded-pill" id="user_title" name="title" placeholder="Notification title" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="user_body" class="form-label fw-medium">Message</label>
                                                <textarea class="form-control" id="user_body" name="body" rows="4" placeholder="Notification message" required></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="user_data" class="form-label fw-medium">Additional Data (JSON)</label>
                                                <textarea class="form-control" id="user_data" name="data" rows="3" placeholder='{"key": "value", "another_key": "another_value"}'></textarea>
                                                <div class="form-text">Optional JSON data to send with the notification</div>
                                            </div>
                                            
                                            <!-- Schedule Options -->
                                            <div class="mb-3">
                                                <label class="form-label fw-medium">When to Send</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="user_schedule_type" id="user_immediate" value="immediate" checked onchange="toggleScheduleOptions('user')">
                                                        <label class="form-check-label" for="user_immediate">
                                                            <i class="fas fa-bolt me-1 text-warning"></i> Send Immediately
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="user_schedule_type" id="user_scheduled" value="scheduled" onchange="toggleScheduleOptions('user')">
                                                        <label class="form-check-label" for="user_scheduled">
                                                            <i class="fas fa-clock me-1 text-primary"></i> Schedule for Later
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div id="user_schedule_options" class="mb-3" style="display: none;">
                                                <label for="user_scheduled_at" class="form-label fw-medium">Schedule Date & Time <span class="text-danger">*</span></label>
                                                <input type="datetime-local" class="form-control rounded-pill" id="user_scheduled_at" name="scheduled_at">
                                                <div class="form-text">Select when you want this notification to be sent</div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-theme rounded-pill px-4" id="sendToUserBtn">
                                                    <i class="fas fa-paper-plane me-2"></i><span class="btn-text">Send Notification</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Send to Group Tab -->
                                    <div class="tab-pane fade" id="group" role="tabpanel" aria-labelledby="group-tab">
                                        <form id="sendToGroupForm">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="user_group_id" class="form-label fw-medium">Select User Group</label>
                                                <select class="form-select rounded-pill" id="user_group_id" name="user_group_id" required>
                                                    <option value="">Choose a user group</option>
                                                    @foreach($userGroups as $group)
                                                        <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->users->count() }} members)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="group_title" class="form-label fw-medium">Title</label>
                                                <input type="text" class="form-control rounded-pill" id="group_title" name="title" placeholder="Notification title" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="group_body" class="form-label fw-medium">Message</label>
                                                <textarea class="form-control" id="group_body" name="body" rows="4" placeholder="Notification message" required></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="group_data" class="form-label fw-medium">Additional Data (JSON)</label>
                                                <textarea class="form-control" id="group_data" name="data" rows="3" placeholder='{"key": "value", "another_key": "another_value"}'></textarea>
                                                <div class="form-text">Optional JSON data to send with the notification</div>
                                            </div>
                                            
                                            <!-- Schedule Options -->
                                            <div class="mb-3">
                                                <label class="form-label fw-medium">When to Send</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="group_schedule_type" id="group_immediate" value="immediate" checked onchange="toggleScheduleOptions('group')">
                                                        <label class="form-check-label" for="group_immediate">
                                                            <i class="fas fa-bolt me-1 text-warning"></i> Send Immediately
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="group_schedule_type" id="group_scheduled" value="scheduled" onchange="toggleScheduleOptions('group')">
                                                        <label class="form-check-label" for="group_scheduled">
                                                            <i class="fas fa-clock me-1 text-primary"></i> Schedule for Later
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div id="group_schedule_options" class="mb-3" style="display: none;">
                                                <label for="group_scheduled_at" class="form-label fw-medium">Schedule Date & Time <span class="text-danger">*</span></label>
                                                <input type="datetime-local" class="form-control rounded-pill" id="group_scheduled_at" name="scheduled_at">
                                                <div class="form-text">Select when you want this notification to be sent</div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-theme rounded-pill px-4" id="sendToGroupBtn">
                                                    <i class="fas fa-paper-plane me-2"></i><span class="btn-text">Send Notification</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Send to All Users Tab -->
                                    <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
                                        <div class="alert alert-warning rounded-3 mb-4">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Warning:</strong> This will send a notification to all users in the system. Use with caution.
                                        </div>
                                        
                                        <!-- Device Token Counts -->
                                        <div class="alert alert-info rounded-3 mb-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Recipients with registered devices:</strong>
                                            <div class="d-flex gap-3 mt-2">
                                                <span class="badge bg-primary rounded-pill px-3 py-2">
                                                    <i class="fas fa-users me-1"></i>
                                                    App Users: {{ $usersWithTokens ?? 0 }}
                                                </span>
                                                <span class="badge bg-success rounded-pill px-3 py-2">
                                                    <i class="fas fa-store me-1"></i>
                                                    Vendor Customers: {{ $vendorCustomersWithTokens ?? 0 }}
                                                </span>
                                                <span class="badge bg-secondary rounded-pill px-3 py-2">
                                                    <i class="fas fa-mobile-alt me-1"></i>
                                                    Total: {{ ($usersWithTokens ?? 0) + ($vendorCustomersWithTokens ?? 0) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <form id="sendToAllForm">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="all_title" class="form-label fw-medium">Title</label>
                                                <input type="text" class="form-control rounded-pill" id="all_title" name="title" placeholder="Notification title" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="all_body" class="form-label fw-medium">Message</label>
                                                <textarea class="form-control" id="all_body" name="body" rows="4" placeholder="Notification message" required></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="all_data" class="form-label fw-medium">Additional Data (JSON)</label>
                                                <textarea class="form-control" id="all_data" name="data" rows="3" placeholder='{"key": "value", "another_key": "another_value"}'></textarea>
                                                <div class="form-text">Optional JSON data to send with the notification</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="exclude_admins" name="exclude_admins" value="1">
                                                    <label class="form-check-label" for="exclude_admins">
                                                        Exclude admin users
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="include_vendor_customers" name="include_vendor_customers" value="1" checked>
                                                    <label class="form-check-label" for="include_vendor_customers">
                                                        Include vendor customers <span class="text-muted">({{ $vendorCustomersWithTokens ?? 0 }} with devices)</span>
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <!-- Schedule Options -->
                                            <div class="mb-3">
                                                <label class="form-label fw-medium">When to Send</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="all_schedule_type" id="all_immediate" value="immediate" checked onchange="toggleScheduleOptions('all')">
                                                        <label class="form-check-label" for="all_immediate">
                                                            <i class="fas fa-bolt me-1 text-warning"></i> Send Immediately
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="all_schedule_type" id="all_scheduled" value="scheduled" onchange="toggleScheduleOptions('all')">
                                                        <label class="form-check-label" for="all_scheduled">
                                                            <i class="fas fa-clock me-1 text-primary"></i> Schedule for Later
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div id="all_schedule_options" class="mb-3" style="display: none;">
                                                <label for="all_scheduled_at" class="form-label fw-medium">Schedule Date & Time <span class="text-danger">*</span></label>
                                                <input type="datetime-local" class="form-control rounded-pill" id="all_scheduled_at" name="scheduled_at">
                                                <div class="form-text">Select when you want this notification to be sent</div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-danger rounded-pill px-4" id="sendToAllBtn">
                                                    <i class="fas fa-paper-plane me-2"></i><span class="btn-text">Send to All Users</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Send to Device Token Tab -->
                                    <div class="tab-pane fade" id="token" role="tabpanel" aria-labelledby="token-tab">
                                        <div class="alert alert-info rounded-3 mb-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Direct Device Token:</strong> Send a notification directly to a specific FCM device token. Useful for testing.
                                        </div>
                                        <form id="sendToTokenForm">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="device_token" class="form-label fw-medium">Device Token (FCM)</label>
                                                <textarea class="form-control" id="device_token" name="device_token" rows="3" placeholder="Enter the FCM device token" required></textarea>
                                                <div class="form-text">The FCM registration token from the mobile device</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="token_title" class="form-label fw-medium">Title</label>
                                                <input type="text" class="form-control rounded-pill" id="token_title" name="title" placeholder="Notification title" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="token_body" class="form-label fw-medium">Message</label>
                                                <textarea class="form-control" id="token_body" name="body" rows="4" placeholder="Notification message" required></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="token_data" class="form-label fw-medium">Additional Data (JSON)</label>
                                                <textarea class="form-control" id="token_data" name="data" rows="3" placeholder='{"key": "value", "another_key": "another_value"}'></textarea>
                                                <div class="form-text">Optional JSON data to send with the notification</div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary rounded-pill px-4" id="sendToTokenBtn">
                                                    <i class="fas fa-paper-plane me-2"></i>Send to Device
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Results Section -->
                                <div class="mt-5" id="notificationResults" style="display: none;">
                                    <h5 class="fw-bold mb-3">Notification Results</h5>
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div id="resultsContent"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Scheduled & Sent Notifications History Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                                    <div>
                                        <h4 class="card-title mb-0 fw-bold">
                                            <i class="fas fa-history me-2 text-primary"></i>Scheduled & Sent Notifications
                                        </h4>
                                        <p class="mb-0 text-muted">View all admin notifications history</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <!-- Status Filter -->
                                        <select class="form-select form-select-sm rounded-pill" id="statusFilter" style="width: auto;">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="sent">Sent</option>
                                            <option value="failed">Failed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                        <!-- Target Type Filter -->
                                        <select class="form-select form-select-sm rounded-pill" id="targetTypeFilter" style="width: auto;">
                                            <option value="">All Targets</option>
                                            <option value="user">Single User</option>
                                            <option value="group">User Group</option>
                                            <option value="all_users">All Users</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" id="refreshNotificationsBtn">
                                            <i class="fas fa-sync-alt me-1"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="notificationsHistoryTable" style="width: 100%;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 25%;">Title</th>
                                                <th style="width: 15%;">Target</th>
                                                <th style="width: 18%;">Scheduled For</th>
                                                <th style="width: 12%;">Status</th>
                                                <th style="width: 12%;">Results</th>
                                                <th style="width: 18%;" class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded via DataTables AJAX -->
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

<!-- Edit Notification Modal -->
<div class="modal fade" id="editNotificationModal" tabindex="-1" aria-labelledby="editNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editNotificationModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Scheduled Notification
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editNotificationForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_notification_id">
                    
                    <div class="mb-3">
                        <label for="edit_title" class="form-label fw-medium">Title</label>
                        <input type="text" class="form-control rounded-pill" id="edit_title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_body" class="form-label fw-medium">Message</label>
                        <textarea class="form-control" id="edit_body" name="body" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_scheduled_at" class="form-label fw-medium">Schedule Date & Time</label>
                        <input type="datetime-local" class="form-control rounded-pill" id="edit_scheduled_at" name="scheduled_at" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill" id="saveEditBtn">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Notification Details Modal -->
<div class="modal fade" id="viewNotificationModal" tabindex="-1" aria-labelledby="viewNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewNotificationModalLabel">
                    <i class="fas fa-bell me-2"></i>Notification Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewNotificationContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Note: toggleScheduleOptions and setMinDateTime are defined inline in the HTML for early availability
    
    // Initialize on page load - ALL handlers must be inside document.ready
    $(document).ready(function() {
        setMinDateTime();
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize Notifications History DataTable (global for access from other functions)
        window.notificationsTable = $('#notificationsHistoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.firebase.notifications.data") }}',
                data: function(d) {
                    d.status = $('#statusFilter').val();
                    d.target_type = $('#targetTypeFilter').val();
                }
            },
            columns: [
                { data: 'title', name: 'title' },
                { data: 'target', name: 'target_type', orderable: false, searchable: false },
                { data: 'scheduled_at', name: 'scheduled_at' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'results', name: 'results', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[2, 'desc']], // Order by scheduled_at descending
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading...',
                emptyTable: '<div class="text-center py-4"><i class="fas fa-bell-slash fa-3x text-muted mb-3"></i><p class="text-muted mb-0">No notifications found</p></div>',
                zeroRecords: '<div class="text-center py-4"><i class="fas fa-search fa-3x text-muted mb-3"></i><p class="text-muted mb-0">No matching notifications found</p></div>'
            },
            drawCallback: function() {
                // Re-initialize tooltips after table redraw
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
        
        // Adjust DataTables select width
        $('.dataTables_length select').css('width', '80px');
        
        // Filter change handlers
        $('#statusFilter, #targetTypeFilter').on('change', function() {
            window.notificationsTable.ajax.reload();
        });
        
        // Refresh button handler
        $('#refreshNotificationsBtn').on('click', function() {
            window.notificationsTable.ajax.reload();
        });
    
        // Handle send to user form submission
        $('#sendToUserForm').on('submit', function(e) {
            e.preventDefault();
            
            // Get schedule type
            const scheduleType = $('input[name="user_schedule_type"]:checked').val();
            const scheduledAt = $('#user_scheduled_at').val();
            
            // Validate scheduled datetime if scheduling
            if (scheduleType === 'scheduled' && !scheduledAt) {
                alert('Please select a date and time for the scheduled notification.');
                return;
            }
            
            // Disable submit button and show loading
            const submitBtn = $('#sendToUserBtn');
            const originalText = submitBtn.html();
            const loadingText = scheduleType === 'scheduled' 
                ? '<i class="fas fa-spinner fa-spin me-2"></i>Scheduling...'
                : '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            submitBtn.html(loadingText);
            submitBtn.prop('disabled', true);
            
            // Hide previous results
            $('#notificationResults').hide();
            
            // Parse JSON data if provided
            let additionalData = null;
            const dataStr = $('#user_data').val();
            if (dataStr) {
                try {
                    additionalData = JSON.parse(dataStr);
                } catch (e) {
                    alert('Invalid JSON in Additional Data field: ' + e.message);
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                    return;
                }
            }
            
            // Get form data
            const formData = {
                user_id: $('#user_id').val(),
                title: $('#user_title').val(),
                body: $('#user_body').val(),
                data: additionalData,
                schedule_type: scheduleType,
                scheduled_at: scheduledAt,
                _token: $('input[name="_token"]').val()
            };
            
            // Send AJAX request
            $.ajax({
                url: '{{ route("admin.firebase.notifications.user") }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.scheduled) {
                        displayScheduledResult(response, 'User Notification');
                    } else {
                        displayResults(response, 'User Notification');
                    }
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                    
                    // Reset schedule type to immediate
                    $('#user_immediate').prop('checked', true);
                    toggleScheduleOptions('user');
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while processing the notification.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    displayError(errorMessage);
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                }
            });
        }); // End of sendToUserForm handler
    
        // Handle send to group form submission
        $('#sendToGroupForm').on('submit', function(e) {
            e.preventDefault();
            
            // Get schedule type
            const scheduleType = $('input[name="group_schedule_type"]:checked').val();
            const scheduledAt = $('#group_scheduled_at').val();
            
            // Validate scheduled datetime if scheduling
            if (scheduleType === 'scheduled' && !scheduledAt) {
                alert('Please select a date and time for the scheduled notification.');
                return;
            }
            
            // Disable submit button and show loading
            const submitBtn = $('#sendToGroupBtn');
            const originalText = submitBtn.html();
            const loadingText = scheduleType === 'scheduled' 
                ? '<i class="fas fa-spinner fa-spin me-2"></i>Scheduling...'
                : '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            submitBtn.html(loadingText);
            submitBtn.prop('disabled', true);
            
            // Hide previous results
            $('#notificationResults').hide();
            
            // Parse JSON data if provided
            let additionalData = null;
            const dataStr = $('#group_data').val();
            if (dataStr) {
                try {
                    additionalData = JSON.parse(dataStr);
                } catch (e) {
                    alert('Invalid JSON in Additional Data field: ' + e.message);
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                    return;
                }
            }
            
            // Get form data
            const formData = {
                user_group_id: $('#user_group_id').val(),
                title: $('#group_title').val(),
                body: $('#group_body').val(),
                data: additionalData,
                schedule_type: scheduleType,
                scheduled_at: scheduledAt,
                _token: $('input[name="_token"]').val()
            };
            
            // Send AJAX request
            $.ajax({
                url: '{{ route("admin.firebase.notifications.group") }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.scheduled) {
                        displayScheduledResult(response, 'Group Notification');
                    } else {
                        displayResults(response, 'Group Notification');
                    }
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                    
                    // Reset schedule type to immediate
                    $('#group_immediate').prop('checked', true);
                    toggleScheduleOptions('group');
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while processing the notification.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    displayError(errorMessage);
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                }
            });
        }); // End of sendToGroupForm handler
    
        // Handle send to all users form submission
        $('#sendToAllForm').on('submit', function(e) {
            e.preventDefault();
            
            // Get schedule type
            const scheduleType = $('input[name="all_schedule_type"]:checked').val();
            const scheduledAt = $('#all_scheduled_at').val();
            
            // Validate scheduled datetime if scheduling
            if (scheduleType === 'scheduled' && !scheduledAt) {
                alert('Please select a date and time for the scheduled notification.');
                return;
            }
            
            // Confirm before sending/scheduling
            const confirmMessage = scheduleType === 'scheduled'
                ? 'Are you sure you want to schedule this notification for ALL users?'
                : 'Are you sure you want to send this notification to ALL users? This action cannot be undone.';
            
            if (!confirm(confirmMessage)) {
                return;
            }
            
            // Disable submit button and show loading
            const submitBtn = $('#sendToAllBtn');
            const originalText = submitBtn.html();
            const loadingText = scheduleType === 'scheduled' 
                ? '<i class="fas fa-spinner fa-spin me-2"></i>Scheduling...'
                : '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            submitBtn.html(loadingText);
            submitBtn.prop('disabled', true);
            
            // Hide previous results
            $('#notificationResults').hide();
            
            // Parse JSON data if provided
            let additionalData = null;
            const dataStr = $('#all_data').val();
            if (dataStr) {
                try {
                    additionalData = JSON.parse(dataStr);
                } catch (e) {
                    alert('Invalid JSON in Additional Data field: ' + e.message);
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                    return;
                }
            }
            
            // Get form data
            const formData = {
                title: $('#all_title').val(),
                body: $('#all_body').val(),
                data: additionalData,
                exclude_admins: $('#exclude_admins').is(':checked') ? 1 : 0,
                include_vendor_customers: $('#include_vendor_customers').is(':checked') ? 1 : 0,
                schedule_type: scheduleType,
                scheduled_at: scheduledAt,
                _token: $('input[name="_token"]').val()
            };
            
            // Send AJAX request
            $.ajax({
                url: '{{ route("admin.firebase.notifications.all") }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.scheduled) {
                        displayScheduledResult(response, 'Broadcast Notification');
                    } else {
                        displayResults(response, 'Broadcast Notification');
                    }
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                    
                    // Reset schedule type to immediate
                    $('#all_immediate').prop('checked', true);
                    toggleScheduleOptions('all');
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while processing the notification.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    displayError(errorMessage);
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                }
            });
        }); // End of sendToAllForm handler
        
        // Handle send to device token form submission
        $('#sendToTokenForm').on('submit', function(e) {
            e.preventDefault();
            
            // Disable submit button and show loading
            const submitBtn = $('#sendToTokenBtn');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Sending...');
            submitBtn.prop('disabled', true);
            
            // Hide previous results
            $('#notificationResults').hide();
            
            // Parse JSON data if provided
            let additionalData = {};
            const dataStr = $('#token_data').val();
            if (dataStr) {
                try {
                    additionalData = JSON.parse(dataStr);
                } catch (e) {
                    alert('Invalid JSON in Additional Data field: ' + e.message);
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                    return;
                }
            }
            
            // Get form data
            const formData = {
                device_token: $('#device_token').val().trim(),
                title: $('#token_title').val(),
                body: $('#token_body').val(),
                data: additionalData,
                _token: $('input[name="_token"]').val()
            };
            
            // Send AJAX request
            $.ajax({
                url: '{{ route("admin.firebase.test-notification") }}',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    displayResults(response, 'Device Token Notification');
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while sending the notification.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    displayError(errorMessage);
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                }
            });
        }); // End of sendToTokenForm handler
        
        // Handle edit form submission
        $('#editNotificationForm').on('submit', function(e) {
            e.preventDefault();
            
            const id = $('#edit_notification_id').val();
            const submitBtn = $('#saveEditBtn');
            const originalText = submitBtn.html();
            
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');
            submitBtn.prop('disabled', true);
            
            $.ajax({
                url: '{{ url("admin/firebase/notifications/scheduled") }}/' + id,
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({
                    title: $('#edit_title').val(),
                    body: $('#edit_body').val(),
                    scheduled_at: $('#edit_scheduled_at').val(),
                    _token: $('input[name="_token"]').val()
                }),
                success: function(response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editNotificationModal')).hide();
                        // Refresh DataTable instead of full page reload
                        if (window.notificationsTable) {
                            window.notificationsTable.ajax.reload(null, false);
                        } else {
                            location.reload();
                        }
                    } else {
                        alert('Failed to update notification: ' + (response.message || 'Unknown error'));
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to update notification.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert(errorMessage);
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                }
            });
        }); // End of editNotificationForm handler
    }); // End of document.ready
    
    // Helper function to format date as local datetime string (YYYY-MM-DDTHH:MM)
    function formatLocalDateTime(date) {
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        var hours = String(date.getHours()).padStart(2, '0');
        var minutes = String(date.getMinutes()).padStart(2, '0');
        return year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    }
    
    // Display scheduled result (outside document.ready - called by name)
    function displayScheduledResult(response, type) {
        const scheduledTime = response.scheduled_at ? new Date(response.scheduled_at).toLocaleString() : 'Unknown';
        
        let html = `<div class="alert alert-info rounded-3 px-4 py-3">
                        <i class="fas fa-clock me-2"></i>
                        <strong>${type} scheduled successfully!</strong>
                    </div>
                    <div class="card border-0 bg-light mt-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong><i class="fas fa-heading me-2"></i>Title:</strong></p>
                                    <p class="text-muted">${response.notification?.title || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong><i class="fas fa-calendar-alt me-2"></i>Scheduled For:</strong></p>
                                    <p class="text-muted">${scheduledTime}</p>
                                </div>
                            </div>
                            <p class="mb-0 mt-2">
                                <i class="fas fa-info-circle me-1 text-primary"></i>
                                The notification will be sent automatically at the scheduled time.
                                You can view and manage it in the "Scheduled & Sent Notifications" section below.
                            </p>
                        </div>
                    </div>`;
        
        $('#resultsContent').html(html);
        $('#notificationResults').show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#notificationResults').offset().top - 100
        }, 500);
        
        // Refresh DataTable after 1 second to show the new notification in history
        setTimeout(function() {
            if (window.notificationsTable) {
                window.notificationsTable.ajax.reload(null, false);
            }
        }, 1000);
    }
    
    // Display results
    function displayResults(response, type) {
        let html = '';
        
        // Determine alert type based on success/failure ratio
        const successful = response.summary?.successful || 0;
        const failed = response.summary?.failed || 0;
        const total = response.summary?.total_users || response.summary?.total || (successful + failed);
        
        let alertClass = 'alert-success';
        let alertIcon = 'fa-check-circle';
        let alertTitle = `${type} sent successfully!`;
        
        if (!response.success && successful === 0) {
            alertClass = 'alert-danger';
            alertIcon = 'fa-exclamation-circle';
            alertTitle = `${type} failed`;
        } else if (failed > 0 && successful > 0) {
            alertClass = 'alert-warning';
            alertIcon = 'fa-exclamation-triangle';
            alertTitle = `${type} partially sent`;
        } else if (failed > 0 && successful === 0) {
            alertClass = 'alert-danger';
            alertIcon = 'fa-exclamation-circle';
            alertTitle = `${type} failed`;
        }
        
        html += `<div class="alert ${alertClass} rounded-3 px-4 py-3">
                    <i class="fas ${alertIcon} me-2"></i>
                    <strong>${alertTitle}</strong> ${response.message || ''}
                 </div>`;
                 
        if (response.summary) {
            html += `<div class="row mt-3">
                        <div class="col-md-4">
                            <div class="card border-0 bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-0">${response.summary.total_users || response.summary.total || 0}</h5>
                                    <p class="card-text mb-0">Total Users</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-0">${response.summary.successful || response.summary.success || 0}</h5>
                                    <p class="card-text mb-0">Successful</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-0">${response.summary.failed || 0}</h5>
                                    <p class="card-text mb-0">Failed</p>
                                </div>
                            </div>
                        </div>
                    </div>`;
                    
            // Show breakdown if available (for broadcast notifications)
            if (response.summary.users_breakdown) {
                const appUsers = response.summary.users_breakdown.app_users || {};
                const vendorCustomers = response.summary.users_breakdown.vendor_customers || {};
                
                html += `<div class="row mt-3">
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold mb-3"><i class="fas fa-chart-pie me-2"></i>Breakdown by User Type</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span><i class="fas fa-users text-primary me-2"></i>App Users:</span>
                                                    <span class="badge bg-primary">${appUsers.total_users || 0} total</span>
                                                </div>
                                                <div class="small text-muted">
                                                    <span class="text-success me-2"><i class="fas fa-check me-1"></i>${appUsers.successful || 0} sent</span>
                                                    <span class="text-danger"><i class="fas fa-times me-1"></i>${appUsers.failed || 0} failed</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span><i class="fas fa-store text-success me-2"></i>Vendor Customers:</span>
                                                    <span class="badge bg-success">${vendorCustomers.total_customers || 0} total</span>
                                                </div>
                                                <div class="small text-muted">
                                                    <span class="text-success me-2"><i class="fas fa-check me-1"></i>${vendorCustomers.successful || 0} sent</span>
                                                    <span class="text-danger"><i class="fas fa-times me-1"></i>${vendorCustomers.failed || 0} failed</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
            }
        }
        
        // Always show detailed results if available (for debugging failed notifications)
        if (response.results && response.results.length > 0) {
            html += `<div class="mt-4">
                        <h6 class="fw-bold">Detailed Results:</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>`;
            
            response.results.forEach(function(result) {
                const statusClass = result.success ? 'text-success' : 'text-danger';
                const statusIcon = result.success ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>';
                html += `<tr>
                            <td>${result.user_id || result.customer_id || 'N/A'}</td>
                            <td class="${statusClass}">${statusIcon} ${result.success ? 'Success' : 'Failed'}</td>
                            <td>${result.message || 'No message'}</td>
                         </tr>`;
            });
            
            html += `           </tbody>
                            </table>
                        </div>
                     </div>`;
        }
        
        // Show helpful tips if all failed
        if (!response.success && (response.summary?.failed > 0 || failed > 0)) {
            html += `<div class="alert alert-info mt-3 rounded-3">
                        <h6 class="alert-heading"><i class="fas fa-lightbulb me-2"></i>Troubleshooting Tips</h6>
                        <ul class="mb-0 small">
                            <li>Device tokens may be expired or invalid. Users need to re-login to the app to refresh their tokens.</li>
                            <li>Make sure Firebase is properly configured in your settings.</li>
                            <li>Check the Laravel logs for more detailed error messages.</li>
                            <li>Verify that the Firebase project ID matches the one used in the mobile app.</li>
                        </ul>
                     </div>`;
        }
        
        $('#resultsContent').html(html);
        $('#notificationResults').show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#notificationResults').offset().top - 100
        }, 500);
    }

    // Display error (outside document.ready - called by name)
    function displayError(message) {
        const html = `<div class="alert alert-danger rounded-pill px-4 py-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Error:</strong> ${message}
                      </div>`;
                      
        $('#resultsContent').html(html);
        $('#notificationResults').show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#notificationResults').offset().top - 100
        }, 500);
    }
    
    // Edit notification
    function editNotification(id) {
        // Fetch notification details
        $.ajax({
            url: '{{ url("admin/firebase/notifications/scheduled") }}/' + id,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const notification = response.notification;
                    $('#edit_notification_id').val(notification.id);
                    $('#edit_title').val(notification.title);
                    $('#edit_body').val(notification.body);
                    
                    // Format datetime for input (local time)
                    const scheduledAt = new Date(notification.scheduled_at);
                    const formattedDate = formatLocalDateTime(scheduledAt);
                    $('#edit_scheduled_at').val(formattedDate);
                    
                    // Set minimum datetime (1 minute from now, local time)
                    const now = new Date();
                    now.setMinutes(now.getMinutes() + 1);
                    $('#edit_scheduled_at').attr('min', formatLocalDateTime(now));
                    
                    // Show modal
                    new bootstrap.Modal(document.getElementById('editNotificationModal')).show();
                } else {
                    alert('Failed to load notification details: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert('Failed to load notification details. Please try again.');
            }
        });
    }
    
    // Cancel notification (outside document.ready - called by name from onclick)
    function cancelNotification(id) {
        if (!confirm('Are you sure you want to cancel this scheduled notification?')) {
            return;
        }
        
        $.ajax({
            url: '{{ url("admin/firebase/notifications/scheduled") }}/' + id,
            method: 'DELETE',
            data: {
                _token: $('input[name="_token"]').val()
            },
            success: function(response) {
                if (response.success) {
                    // Refresh DataTable instead of full page reload
                    if (window.notificationsTable) {
                        window.notificationsTable.ajax.reload(null, false);
                    } else {
                        location.reload();
                    }
                } else {
                    alert('Failed to cancel notification: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert('Failed to cancel notification. Please try again.');
            }
        });
    }
    
    // View notification details
    function viewNotificationDetails(id) {
        $.ajax({
            url: '{{ url("admin/firebase/notifications/scheduled") }}/' + id,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const notification = response.notification;
                    const scheduledAt = new Date(notification.scheduled_at).toLocaleString();
                    const sentAt = notification.sent_at ? new Date(notification.sent_at).toLocaleString() : 'N/A';
                    
                    let statusBadge = '';
                    switch(notification.status) {
                        case 'pending':
                            statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
                            break;
                        case 'sent':
                            statusBadge = '<span class="badge bg-success">Sent</span>';
                            break;
                        case 'failed':
                            statusBadge = '<span class="badge bg-danger">Failed</span>';
                            break;
                        case 'cancelled':
                            statusBadge = '<span class="badge bg-secondary">Cancelled</span>';
                            break;
                        default:
                            statusBadge = '<span class="badge bg-light text-dark">' + notification.status + '</span>';
                    }
                    
                    let html = `
                        <div class="mb-3">
                            <label class="fw-bold text-muted small">TITLE</label>
                            <p class="mb-0">${notification.title}</p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold text-muted small">MESSAGE</label>
                            <p class="mb-0">${notification.body}</p>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="fw-bold text-muted small">STATUS</label>
                                <p class="mb-0">${statusBadge}</p>
                            </div>
                            <div class="col-6">
                                <label class="fw-bold text-muted small">TARGET</label>
                                <p class="mb-0">${notification.target_type}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="fw-bold text-muted small">SCHEDULED FOR</label>
                                <p class="mb-0">${scheduledAt}</p>
                            </div>
                            <div class="col-6">
                                <label class="fw-bold text-muted small">SENT AT</label>
                                <p class="mb-0">${sentAt}</p>
                            </div>
                        </div>`;
                    
                    if (notification.status === 'sent') {
                        html += `
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="fw-bold text-muted small">SUCCESS COUNT</label>
                                    <p class="mb-0 text-success"><i class="fas fa-check me-1"></i>${notification.success_count || 0}</p>
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold text-muted small">FAIL COUNT</label>
                                    <p class="mb-0 text-danger"><i class="fas fa-times me-1"></i>${notification.fail_count || 0}</p>
                                </div>
                            </div>`;
                    }
                    
                    if (notification.error_message) {
                        html += `
                            <div class="mb-3">
                                <label class="fw-bold text-muted small">ERROR MESSAGE</label>
                                <p class="mb-0 text-danger">${notification.error_message}</p>
                            </div>`;
                    }
                    
                    $('#viewNotificationContent').html(html);
                    new bootstrap.Modal(document.getElementById('viewNotificationModal')).show();
                } else {
                    alert('Failed to load notification details.');
                }
            },
            error: function(xhr) {
                alert('Failed to load notification details. Please try again.');
            }
        });
    }
</script>
@endsection