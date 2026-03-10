@extends('vendor.layouts.app')

@section('title', 'Push Notifications - ' . config('app.name', 'Laravel'))

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: white;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
    }
    .customer-option-disabled {
        color: #999 !important;
        font-style: italic;
    }
    .customer-has-token {
        color: #198754;
    }
    .customer-no-token {
        color: #dc3545;
    }
    .schedule-options {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .notification-card {
        transition: all 0.2s ease;
    }
    .notification-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    .status-badge {
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
{{-- Inline script for toggle functions - must load before forms --}}
<script>
// Toggle schedule options - defined inline so it's available when radio buttons render
function toggleScheduleOptions(prefix) {
    var radioBtn = document.querySelector('input[name="' + prefix + '_schedule_type"]:checked');
    if (!radioBtn) {
        return;
    }
    
    var scheduleType = radioBtn.value;
    var optionsDiv = document.getElementById(prefix + '_schedule_options');
    var scheduledAtInput = document.getElementById(prefix + '_scheduled_at');
    var btn = document.getElementById(prefix === 'all' ? 'sendToAllBtn' : 'sendToCustomersBtn');
    
    if (!optionsDiv) {
        console.error('Schedule options div not found for prefix:', prefix);
        return;
    }
    
    if (scheduleType === 'scheduled') {
        // Show date-time field when "Schedule for Later" is selected
        optionsDiv.style.display = 'block';
        if (scheduledAtInput) {
            scheduledAtInput.required = true;
        }
        if (btn) {
            btn.innerHTML = '<i class="fas fa-clock me-2"></i><span class="btn-text">' + (prefix === 'all' ? 'Schedule for All Customers' : 'Schedule for Selected Customers') + '</span>';
        }
    } else {
        // Hide date-time field when "Send Immediately" is selected
        optionsDiv.style.display = 'none';
        if (scheduledAtInput) {
            scheduledAtInput.required = false;
            scheduledAtInput.value = '';
        }
        if (btn) {
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i><span class="btn-text">' + (prefix === 'all' ? 'Send to All Customers' : 'Send to Selected Customers') + '</span>';
        }
    }
}

function setMinDateTime(inputId) {
    var now = new Date();
    now.setMinutes(now.getMinutes() + 1); // Allow scheduling 1 minute from now
    
    // Format as local datetime (YYYY-MM-DDTHH:MM) for datetime-local input
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var minDateTime = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    
    var input = document.getElementById(inputId);
    if (input) {
        input.min = minDateTime;
    }
}
</script>

<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', [
                'pageTitle' => 'Push Notifications',
                'breadcrumbs' => [
                    'Push Notifications' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Send Push Notifications</h4>
                                        <p class="mb-0 text-muted small">Send notifications to your customers' mobile devices</p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-info rounded-pill">
                                            <i class="fas fa-mobile-alt me-1"></i>
                                            {{ $customersWithTokens->count() }} devices registered
                                        </span>
                                        <span class="badge bg-secondary rounded-pill">
                                            <i class="fas fa-users me-1"></i>
                                            {{ $customers->count() }} total customers
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Firebase Status Alert -->
                                <div id="firebaseStatusAlert" class="alert alert-info rounded-3 mb-4" style="display: none;">
                                    <i class="fas fa-spinner fa-spin me-2"></i>
                                    Checking Firebase configuration...
                                </div>
                                
                                @if($customers->isEmpty())
                                    <div class="alert alert-warning rounded-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>No Customers Found:</strong> You don't have any customers yet. Customers will appear here once they make purchases or are added to your customer list.
                                    </div>
                                @elseif($customersWithTokens->isEmpty())
                                    <div class="alert alert-warning rounded-3">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>No Devices Registered:</strong> None of your customers have registered their devices for push notifications. They need to log in through the mobile app to receive notifications.
                                    </div>
                                @endif
                                
                                <ul class="nav nav-tabs mb-4" id="notificationTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active rounded-pill px-4" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">
                                            <i class="fas fa-users me-2"></i>Send to All
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link rounded-pill px-4" id="select-tab" data-bs-toggle="tab" data-bs-target="#select" type="button" role="tab" aria-controls="select" aria-selected="false">
                                            <i class="fas fa-user-check me-2"></i>Select Customers
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="notificationTabContent">
                                    <!-- Send to All Customers Tab -->
                                    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                                        <div class="alert alert-info rounded-3 mb-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            This will send a notification to all <strong>{{ $customersWithTokens->count() }}</strong> customers who have registered their devices.
                                        </div>
                                        
                                        <form id="sendToAllForm" method="POST" action="{{ route('vendor.push-notifications.send-all') }}" onsubmit="return handleSendToAllSubmit(event);">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="all_title" class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill" id="all_title" name="title" placeholder="e.g., New Products Available!" required maxlength="255">
                                                <div class="form-text">Keep it short and attention-grabbing (max 255 characters)</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="all_body" class="form-label fw-medium">Message <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="all_body" name="body" rows="4" placeholder="e.g., Check out our latest collection with amazing discounts!" required maxlength="1000"></textarea>
                                                <div class="form-text">The main content of your notification (max 1000 characters)</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="all_data" class="form-label fw-medium">Additional Data (JSON) <span class="text-muted">(Optional)</span></label>
                                                <textarea class="form-control" id="all_data" name="data" rows="3" placeholder='{"screen": "products", "category_id": "123"}'></textarea>
                                                <div class="form-text">Optional JSON data to send with the notification for app navigation</div>
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
                                            
                                            <div id="all_schedule_options" class="schedule-options mb-3" style="display: none;">
                                                <label for="all_scheduled_at" class="form-label fw-medium">Schedule Date & Time <span class="text-danger">*</span></label>
                                                <input type="datetime-local" class="form-control rounded-pill" id="all_scheduled_at" name="scheduled_at" min="">
                                                <div class="form-text">Select when you want this notification to be sent</div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-theme rounded-pill px-4" id="sendToAllBtn" {{ $customersWithTokens->isEmpty() ? 'disabled' : '' }}>
                                                    <i class="fas fa-paper-plane me-2"></i><span class="btn-text">Send to All Customers</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Send to Selected Customers Tab -->
                                    <div class="tab-pane fade" id="select" role="tabpanel" aria-labelledby="select-tab">
                                        <form id="sendToCustomersForm" method="POST" action="{{ route('vendor.push-notifications.send-to-customers') }}" onsubmit="return handleSendToCustomersSubmit(event);">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="customer_ids" class="form-label fw-medium">Select Customers <span class="text-danger">*</span></label>
                                                <select class="form-select" id="customer_ids" name="customer_ids[]" multiple>
                                                    @foreach($customersWithTokens as $customer)
                                                        <option value="{{ $customer->id }}" data-has-token="1">
                                                            {{ $customer->name }} ({{ $customer->email ?? $customer->mobile_number }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="form-text">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Search and select customers to send notifications. Only customers with registered devices are shown.
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="select_title" class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill" id="select_title" name="title" placeholder="e.g., Special Offer Just For You!" required maxlength="255">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="select_body" class="form-label fw-medium">Message <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="select_body" name="body" rows="4" placeholder="e.g., We have a special discount waiting for you. Check it out now!" required maxlength="1000"></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="select_data" class="form-label fw-medium">Additional Data (JSON) <span class="text-muted">(Optional)</span></label>
                                                <textarea class="form-control" id="select_data" name="data" rows="3" placeholder='{"screen": "offers", "offer_id": "456"}'></textarea>
                                                <div class="form-text">Optional JSON data to send with the notification for app navigation</div>
                                            </div>
                                            
                                            <!-- Schedule Options -->
                                            <div class="mb-3">
                                                <label class="form-label fw-medium">When to Send</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="select_schedule_type" id="select_immediate" value="immediate" checked onchange="toggleScheduleOptions('select')">
                                                        <label class="form-check-label" for="select_immediate">
                                                            <i class="fas fa-bolt me-1 text-warning"></i> Send Immediately
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="select_schedule_type" id="select_scheduled" value="scheduled" onchange="toggleScheduleOptions('select')">
                                                        <label class="form-check-label" for="select_scheduled">
                                                            <i class="fas fa-clock me-1 text-primary"></i> Schedule for Later
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div id="select_schedule_options" class="schedule-options mb-3" style="display: none;">
                                                <label for="select_scheduled_at" class="form-label fw-medium">Schedule Date & Time <span class="text-danger">*</span></label>
                                                <input type="datetime-local" class="form-control rounded-pill" id="select_scheduled_at" name="scheduled_at" min="">
                                                <div class="form-text">Select when you want this notification to be sent</div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-theme rounded-pill px-4" id="sendToCustomersBtn" {{ $customersWithTokens->isEmpty() ? 'disabled' : '' }}>
                                                    <i class="fas fa-paper-plane me-2"></i><span class="btn-text">Send to Selected Customers</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Scheduled Notifications List -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                                    <div>
                                        <h5 class="card-title mb-0 fw-bold"><i class="fas fa-calendar-alt me-2"></i>Scheduled & Sent Notifications</h5>
                                        <p class="mb-0 text-muted small">Manage your scheduled notifications and view history</p>
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
                                            <option value="all">All Customers</option>
                                            <option value="selected">Selected Customers</option>
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
                
                <!-- Customer Device Status Card -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Customer Device Status</h5>
                                <p class="mb-0 text-muted small">Overview of customers and their device registration status</p>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Contact</th>
                                                <th>Device Status</th>
                                                <th>Last Login</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($customers as $customer)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($customer->profile_avatar_url)
                                                                <img src="{{ $customer->profile_avatar_url }}" alt="{{ $customer->name }}" class="rounded-circle me-2" width="32" height="32">
                                                            @else
                                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                                    <i class="fas fa-user text-white small"></i>
                                                                </div>
                                                            @endif
                                                            <span>{{ $customer->name }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($customer->email)
                                                            <small class="text-muted">{{ $customer->email }}</small>
                                                        @endif
                                                        @if($customer->mobile_number)
                                                            <br><small class="text-muted">{{ $customer->mobile_number }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($customer->device_token))
                                                            <span class="badge bg-success rounded-pill">
                                                                <i class="fas fa-mobile-alt me-1"></i>Registered
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary rounded-pill">
                                                                <i class="fas fa-times me-1"></i>Not Registered
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($customer->last_login_at)
                                                            <small class="text-muted">{{ $customer->last_login_at->diffForHumans() }}</small>
                                                        @else
                                                            <small class="text-muted">Never</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-4">
                                                        <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                                        No customers found
                                                    </td>
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
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>

<!-- Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="resultModalLabel">Notification Result</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div id="resultIcon" class="mb-3"></div>
                <h5 id="resultTitle" class="mb-2"></h5>
                <p id="resultMessage" class="text-muted mb-0"></p>
                <div id="resultDetails" class="mt-3"></div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-theme rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Notification Modal -->
<div class="modal fade" id="editNotificationModal" tabindex="-1" aria-labelledby="editNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="editNotificationModalLabel"><i class="fas fa-edit me-2"></i>Edit Scheduled Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editNotificationForm">
                    <input type="hidden" id="edit_notification_id">
                    
                    <div class="mb-3">
                        <label for="edit_title" class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-pill" id="edit_title" name="title" required maxlength="255">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_body" class="form-label fw-medium">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_body" name="body" rows="4" required maxlength="1000"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_data" class="form-label fw-medium">Additional Data (JSON) <span class="text-muted">(Optional)</span></label>
                        <textarea class="form-control" id="edit_data" name="data" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_scheduled_at" class="form-label fw-medium">Schedule Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control rounded-pill" id="edit_scheduled_at" name="scheduled_at" required>
                    </div>
                    
                    <div id="edit_customer_selection" class="mb-3" style="display: none;">
                        <label for="edit_customer_ids" class="form-label fw-medium">Select Customers</label>
                        <select class="form-select" id="edit_customer_ids" name="customer_ids[]" multiple>
                            @foreach($customersWithTokens as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->name }} ({{ $customer->email ?? $customer->mobile_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-theme rounded-pill px-4" onclick="saveNotificationEdit()">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div id="confirmIcon" class="mb-3"></div>
                <h5 id="confirmTitle" class="mb-2"></h5>
                <p id="confirmMessage" class="text-muted mb-0"></p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Global error handler for debugging
window.onerror = function(message, source, lineno, colno, error) {
    console.error('Global error:', message, 'at', source, ':', lineno, ':', colno);
    return false;
};

// Global variables
let resultModal, editModal, confirmModal;
let currentEditNotificationId = null;

// Note: toggleScheduleOptions is defined inline in the HTML head for early availability

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Push Notifications page loaded');
    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
    console.log('jQuery available:', typeof jQuery !== 'undefined');
    
    // Initialize Bootstrap modals
    const resultModalElement = document.getElementById('resultModal');
    const editModalElement = document.getElementById('editNotificationModal');
    const confirmModalElement = document.getElementById('confirmModal');
    
    console.log('Modal elements found:', {
        result: !!resultModalElement,
        edit: !!editModalElement,
        confirm: !!confirmModalElement
    });
    
    if (resultModalElement) {
        resultModal = new bootstrap.Modal(resultModalElement);
        console.log('resultModal initialized');
    }
    if (editModalElement) {
        editModal = new bootstrap.Modal(editModalElement);
        console.log('editModal initialized');
    }
    if (confirmModalElement) {
        confirmModal = new bootstrap.Modal(confirmModalElement);
        console.log('confirmModal initialized');
    }
    
    // Initialize Select2 for customer selection
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        $('#customer_ids').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select customers...',
            allowClear: true,
            width: '100%',
            closeOnSelect: false,
            templateResult: formatCustomerOption,
            templateSelection: formatCustomerSelection
        });
        
        $('#edit_customer_ids').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select customers...',
            allowClear: true,
            width: '100%',
            closeOnSelect: false,
            dropdownParent: $('#editNotificationModal')
        });
    }
    
    // Set minimum datetime for schedule inputs
    setMinDateTime();
    
    // Initialize schedule options visibility (ensure hidden on page load)
    toggleScheduleOptions('all');
    toggleScheduleOptions('select');
    
    // Check Firebase status on page load
    checkFirebaseStatus();
    
    // Initialize Notifications History DataTable (global for access from other functions)
    window.notificationsTable = $('#notificationsHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("vendor.push-notifications.data") }}',
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
});

function setMinDateTime() {
    const now = new Date();
    now.setMinutes(now.getMinutes() + 5); // At least 5 minutes from now
    
    // Format as local datetime (YYYY-MM-DDTHH:MM) for datetime-local input
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const minDateTime = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    
    document.getElementById('all_scheduled_at').min = minDateTime;
    document.getElementById('select_scheduled_at').min = minDateTime;
    document.getElementById('edit_scheduled_at').min = minDateTime;
}

function formatCustomerOption(customer) {
    if (!customer.id) {
        return customer.text;
    }
    var $option = $(
        '<span><i class="fas fa-mobile-alt text-success me-2"></i>' + customer.text + '</span>'
    );
    return $option;
}

function formatCustomerSelection(customer) {
    return customer.text;
}

// Handle Send to All form submission
function handleSendToAllSubmit(e) {
    e.preventDefault();
    
    const btn = document.getElementById('sendToAllBtn');
    const originalText = btn.innerHTML;
    const scheduleType = document.querySelector('input[name="all_schedule_type"]:checked').value;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + (scheduleType === 'scheduled' ? 'Scheduling...' : 'Sending...');
    btn.disabled = true;
    
    // Validate JSON if provided
    const dataField = document.getElementById('all_data').value;
    if (dataField && dataField.trim() !== '') {
        try {
            JSON.parse(dataField);
        } catch (err) {
            showResult(false, 'Invalid JSON', 'The additional data field contains invalid JSON. Please check the format.');
            btn.innerHTML = originalText;
            btn.disabled = false;
            return false;
        }
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        showResult(false, 'Error', 'CSRF token not found. Please refresh the page and try again.');
        btn.innerHTML = originalText;
        btn.disabled = false;
        return false;
    }
    
    const requestData = {
        title: document.getElementById('all_title').value,
        body: document.getElementById('all_body').value,
        data: dataField && dataField.trim() !== '' ? dataField : null,
        schedule_type: scheduleType,
        scheduled_at: scheduleType === 'scheduled' ? document.getElementById('all_scheduled_at').value : null
    };
    
    console.log('Sending notification to all customers:', requestData);
    
    fetch('{{ route("vendor.push-notifications.send-all") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Server error');
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (scheduleType === 'scheduled') {
            showResult(data.success, data.success ? 'Notification Scheduled!' : 'Scheduling Failed', data.message);
        } else {
            showResult(data.success, data.success ? 'Notifications Sent!' : 'Sending Failed', data.message, data.summary);
        }
        if (data.success) {
            document.getElementById('sendToAllForm').reset();
            document.getElementById('all_schedule_options').classList.remove('show');
            if (scheduleType === 'scheduled') {
                // Refresh DataTable instead of full page reload
                if (window.notificationsTable) {
                    setTimeout(() => window.notificationsTable.ajax.reload(null, false), 500);
                }
            }
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showResult(false, 'Error', 'An error occurred: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
    
    return false;
}

// Handle Send to Selected Customers form submission
function handleSendToCustomersSubmit(e) {
    e.preventDefault();
    
    const btn = document.getElementById('sendToCustomersBtn');
    const originalText = btn.innerHTML;
    const scheduleType = document.querySelector('input[name="select_schedule_type"]:checked').value;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + (scheduleType === 'scheduled' ? 'Scheduling...' : 'Sending...');
    btn.disabled = true;
    
    // Get selected customers from Select2
    let selectedCustomers = [];
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        selectedCustomers = $('#customer_ids').val() || [];
    } else {
        const selectElement = document.getElementById('customer_ids');
        selectedCustomers = Array.from(selectElement.selectedOptions).map(opt => opt.value);
    }
    
    if (!selectedCustomers || selectedCustomers.length === 0) {
        showResult(false, 'No Customers Selected', 'Please select at least one customer to send the notification to.');
        btn.innerHTML = originalText;
        btn.disabled = false;
        return false;
    }
    
    // Validate JSON if provided
    const dataField = document.getElementById('select_data').value;
    if (dataField && dataField.trim() !== '') {
        try {
            JSON.parse(dataField);
        } catch (err) {
            showResult(false, 'Invalid JSON', 'The additional data field contains invalid JSON. Please check the format.');
            btn.innerHTML = originalText;
            btn.disabled = false;
            return false;
        }
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        showResult(false, 'Error', 'CSRF token not found. Please refresh the page and try again.');
        btn.innerHTML = originalText;
        btn.disabled = false;
        return false;
    }
    
    const requestData = {
        customer_ids: selectedCustomers.map(id => parseInt(id)),
        title: document.getElementById('select_title').value,
        body: document.getElementById('select_body').value,
        data: dataField && dataField.trim() !== '' ? dataField : null,
        schedule_type: scheduleType,
        scheduled_at: scheduleType === 'scheduled' ? document.getElementById('select_scheduled_at').value : null
    };
    
    console.log('Sending notification to selected customers:', requestData);
    
    fetch('{{ route("vendor.push-notifications.send-to-customers") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Server error');
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (scheduleType === 'scheduled') {
            showResult(data.success, data.success ? 'Notification Scheduled!' : 'Scheduling Failed', data.message);
        } else {
            showResult(data.success, data.success ? 'Notifications Sent!' : 'Sending Failed', data.message, data.summary);
        }
        if (data.success) {
            document.getElementById('sendToCustomersForm').reset();
            document.getElementById('select_schedule_options').classList.remove('show');
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                $('#customer_ids').val(null).trigger('change');
            }
            if (scheduleType === 'scheduled') {
                // Refresh DataTable instead of full page reload
                if (window.notificationsTable) {
                    setTimeout(() => window.notificationsTable.ajax.reload(null, false), 500);
                }
            }
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showResult(false, 'Error', 'An error occurred: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
    
    return false;
}

// Edit notification
function editNotification(id) {
    console.log('editNotification called with id:', id);
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const url = `{{ url('vendor/push-notifications/scheduled') }}/${id}`;
    console.log('Fetching URL:', url);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
        }
    })
    .then(response => {
        console.log('Edit response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Response text:', text);
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Edit response data:', data);
        if (data.success) {
            const notification = data.notification;
            
            document.getElementById('edit_notification_id').value = notification.id;
            document.getElementById('edit_title').value = notification.title;
            document.getElementById('edit_body').value = notification.body;
            document.getElementById('edit_data').value = notification.data || '';
            document.getElementById('edit_scheduled_at').value = notification.scheduled_at;
            
            // Show/hide customer selection based on target type
            const customerSelectionDiv = document.getElementById('edit_customer_selection');
            if (notification.target_type === 'selected') {
                customerSelectionDiv.style.display = 'block';
                if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                    $('#edit_customer_ids').val(notification.customer_ids).trigger('change');
                }
            } else {
                customerSelectionDiv.style.display = 'none';
            }
            
            currentEditNotificationId = notification.id;
            editModal.show();
        } else {
            showResult(false, 'Error', data.message || 'Unknown error');
        }
    })
    .catch(error => {
        console.error('Edit error:', error);
        showResult(false, 'Error', 'Failed to load notification details: ' + error.message);
    });
}

// Save notification edit
function saveNotificationEdit() {
    const id = document.getElementById('edit_notification_id').value;
    console.log('saveNotificationEdit called with id:', id);
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    // Validate JSON if provided
    const dataField = document.getElementById('edit_data').value;
    if (dataField && dataField.trim() !== '') {
        try {
            JSON.parse(dataField);
        } catch (err) {
            showResult(false, 'Invalid JSON', 'The additional data field contains invalid JSON.');
            return;
        }
    }
    
    let customerIds = null;
    if (document.getElementById('edit_customer_selection').style.display !== 'none') {
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            customerIds = $('#edit_customer_ids').val();
        }
    }
    
    const requestData = {
        title: document.getElementById('edit_title').value,
        body: document.getElementById('edit_body').value,
        data: dataField && dataField.trim() !== '' ? dataField : null,
        scheduled_at: document.getElementById('edit_scheduled_at').value,
        customer_ids: customerIds
    };
    
    console.log('Update request data:', requestData);
    const url = `{{ url('vendor/push-notifications/scheduled') }}/${id}`;
    console.log('Update URL:', url);
    
    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Update response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Response text:', text);
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Update response data:', data);
        editModal.hide();
        showResult(data.success, data.success ? 'Updated!' : 'Update Failed', data.message || 'Unknown error');
        if (data.success) {
            // Refresh DataTable instead of full page reload
            if (window.notificationsTable) {
                setTimeout(() => window.notificationsTable.ajax.reload(null, false), 500);
            }
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        editModal.hide();
        showResult(false, 'Error', 'Failed to update notification: ' + error.message);
    });
}

// Cancel notification
function cancelNotification(id) {
    console.log('cancelNotification called with id:', id);
    showConfirm(
        '<i class="fas fa-ban text-warning fa-4x"></i>',
        'Cancel Notification?',
        'This notification will not be sent. You can still delete it later.',
        function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const url = `{{ url('vendor/push-notifications/scheduled') }}/${id}/cancel`;
            console.log('Cancel URL:', url);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Cancel response status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Response text:', text);
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Cancel response data:', data);
                confirmModal.hide();
                showResult(data.success, data.success ? 'Cancelled!' : 'Cancel Failed', data.message || 'Unknown error');
                if (data.success) {
                    // Refresh DataTable instead of full page reload
                    if (window.notificationsTable) {
                        setTimeout(() => window.notificationsTable.ajax.reload(null, false), 500);
                    }
                }
            })
            .catch(error => {
                console.error('Cancel error:', error);
                confirmModal.hide();
                showResult(false, 'Error', 'Failed to cancel notification: ' + error.message);
            });
        }
    );
}

// Delete notification
function deleteNotification(id) {
    console.log('deleteNotification called with id:', id);
    showConfirm(
        '<i class="fas fa-trash text-danger fa-4x"></i>',
        'Delete Notification?',
        'This action cannot be undone. The notification record will be permanently deleted.',
        function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const url = `{{ url('vendor/push-notifications/scheduled') }}/${id}`;
            console.log('Delete URL:', url);
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Delete response status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Response text:', text);
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Delete response data:', data);
                confirmModal.hide();
                showResult(data.success, data.success ? 'Deleted!' : 'Delete Failed', data.message || 'Unknown error');
                if (data.success) {
                    // Refresh DataTable instead of manually removing row
                    if (window.notificationsTable) {
                        setTimeout(() => window.notificationsTable.ajax.reload(null, false), 500);
                    }
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                confirmModal.hide();
                showResult(false, 'Error', 'Failed to delete notification: ' + error.message);
            });
        }
    );
}

function showConfirm(icon, title, message, onConfirm) {
    document.getElementById('confirmIcon').innerHTML = icon;
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    
    const confirmBtn = document.getElementById('confirmActionBtn');
    confirmBtn.onclick = onConfirm;
    
    confirmModal.show();
}

function showResult(success, title, message, summary = null) {
    const icon = success 
        ? '<i class="fas fa-check-circle text-success fa-4x"></i>'
        : '<i class="fas fa-times-circle text-danger fa-4x"></i>';
    
    document.getElementById('resultIcon').innerHTML = icon;
    document.getElementById('resultTitle').textContent = title;
    document.getElementById('resultMessage').textContent = message;
    
    let detailsHtml = '';
    if (summary) {
        detailsHtml = `
            <div class="d-flex justify-content-center gap-3 mt-3">
                <div class="text-center">
                    <div class="h4 mb-0 text-success">${summary.successful || 0}</div>
                    <small class="text-muted">Sent</small>
                </div>
                <div class="text-center">
                    <div class="h4 mb-0 text-danger">${summary.failed || 0}</div>
                    <small class="text-muted">Failed</small>
                </div>
                ${summary.no_device_token ? `
                <div class="text-center">
                    <div class="h4 mb-0 text-warning">${summary.no_device_token}</div>
                    <small class="text-muted">No Device</small>
                </div>
                ` : ''}
            </div>
        `;
    }
    document.getElementById('resultDetails').innerHTML = detailsHtml;
    
    if (resultModal) {
        resultModal.show();
    } else {
        alert(title + '\n' + message);
    }
}

function checkFirebaseStatus() {
    const alertDiv = document.getElementById('firebaseStatusAlert');
    if (!alertDiv) return;
    
    alertDiv.style.display = 'block';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    fetch('{{ route("vendor.push-notifications.firebase-status") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to check Firebase status');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alertDiv.className = 'alert alert-success rounded-3 mb-4';
            alertDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i><strong>Firebase Connected:</strong> Push notifications are ready to send.';
        } else {
            alertDiv.className = 'alert alert-danger rounded-3 mb-4';
            alertDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i><strong>Firebase Not Configured:</strong> ' + data.message + ' Please contact your administrator.';
            // Disable send buttons
            const sendToAllBtn = document.getElementById('sendToAllBtn');
            const sendToCustomersBtn = document.getElementById('sendToCustomersBtn');
            if (sendToAllBtn) sendToAllBtn.disabled = true;
            if (sendToCustomersBtn) sendToCustomersBtn.disabled = true;
        }
    })
    .catch(error => {
        console.error('Error checking Firebase status:', error);
        alertDiv.className = 'alert alert-warning rounded-3 mb-4';
        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Could not verify Firebase configuration.';
    });
}
</script>
@endsection
