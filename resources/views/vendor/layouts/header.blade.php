<!-- Vendor Header -->
<header class="bg-surface border-bottom border-default shadow-sm py-3">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <!-- Mobile sidebar toggle (visible only on small screens) -->
                <button id="sidebar-toggle" class="btn btn-outline-secondary me-3 rounded-circle d-md-none" type="button" style="width: 40px; height: 40px;">
                    <i class="fas fa-bars"></i>
                </button>
                <!-- Desktop sidebar toggle (visible only on medium+ screens) -->
                <button id="desktop-sidebar-toggle" class="btn btn-outline-secondary me-3 rounded-circle" type="button" title="Toggle sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div>
                    <h1 class="h4 mb-0 fw-semibold">@yield('page-title', 'Dashboard')</h1>
                    <!-- Breadcrumbs -->
                    @if (isset($breadcrumbs) && is_array($breadcrumbs))
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Home</a></li>
                                @foreach ($breadcrumbs as $label => $url)
                                    @if (is_string($label) && $url)
                                        <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                                    @else
                                        <li class="breadcrumb-item active" aria-current="page">{{ $url }}</li>
                                    @endif
                                @endforeach
                            </ol>
                        </nav>
                    @endif
                </div>
            </div>
            
            <div class="d-flex align-items-center">
                <!-- Lead Reminders -->
                @php
                    $vendorId = null;
                    if (Auth::check()) {
                        if (Auth::user()->vendor) {
                            $vendorId = Auth::user()->vendor->id;
                        } elseif (Auth::user()->vendorStaff && Auth::user()->vendorStaff->vendor) {
                            $vendorId = Auth::user()->vendorStaff->vendor->id;
                        }
                    }
                    
                    $dueReminders = collect();
                    $upcomingReminders = collect();
                    $dueRemindersCount = 0;
                    $totalPendingCount = 0;
                    
                    if ($vendorId) {
                        // Get overdue reminders
                        $dueReminders = \App\Models\LeadReminder::where('vendor_id', $vendorId)
                            ->where('status', 'pending')
                            ->where('reminder_at', '<=', now())
                            ->whereHas('lead')
                            ->with(['lead' => function($query) {
                                $query->withTrashed();
                            }])
                            ->orderBy('reminder_at', 'asc')
                            ->limit(5)
                            ->get();
                        $dueRemindersCount = $dueReminders->count();
                        
                        // Get upcoming reminders (next 24 hours)
                        $upcomingReminders = \App\Models\LeadReminder::where('vendor_id', $vendorId)
                            ->where('status', 'pending')
                            ->where('reminder_at', '>', now())
                            ->where('reminder_at', '<=', now()->addHours(24))
                            ->whereHas('lead')
                            ->with(['lead' => function($query) {
                                $query->withTrashed();
                            }])
                            ->orderBy('reminder_at', 'asc')
                            ->limit(3)
                            ->get();
                            
                        $totalPendingCount = \App\Models\LeadReminder::where('vendor_id', $vendorId)
                            ->where('status', 'pending')
                            ->count();
                    }
                @endphp
                @if($vendorId)
                <div class="dropdown me-2">
                    <button class="btn btn-outline-{{ $dueRemindersCount > 0 ? 'danger' : ($upcomingReminders->count() > 0 ? 'warning' : 'secondary') }} position-relative rounded-circle" type="button" id="remindersDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Lead Reminders">
                        <i class="fas fa-calendar-check {{ $dueRemindersCount > 0 ? 'fa-shake' : '' }}"></i>
                        @if($dueRemindersCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $dueRemindersCount }}
                            </span>
                        @elseif($upcomingReminders->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                {{ $upcomingReminders->count() }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow mt-2 p-0" aria-labelledby="remindersDropdown" style="min-width: 380px; max-height: 450px; overflow-y: auto;">
                        <!-- Header -->
                        <li class="bg-light border-bottom">
                            <div class="dropdown-header py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark"><i class="fas fa-calendar-check me-2"></i>Lead Reminders</span>
                                    @if($totalPendingCount > 0)
                                        <span class="badge bg-primary rounded-pill">{{ $totalPendingCount }} pending</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                        
                        @if($dueReminders->count() > 0)
                            <!-- Overdue Section -->
                            <li class="bg-danger-subtle">
                                <div class="dropdown-header py-1 small fw-semibold text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>OVERDUE
                                </div>
                            </li>
                            @foreach($dueReminders as $reminder)
                                @if($reminder->lead)
                                <li>
                                    <a class="dropdown-item d-flex align-items-start py-2 border-bottom bg-danger-subtle bg-opacity-25" href="{{ route('vendor.leads.show', $reminder->lead_id) }}">
                                        <div class="rounded-circle bg-danger text-white me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-semibold text-truncate text-danger">{{ $reminder->title }}</div>
                                            <small class="text-secondary d-block text-truncate">
                                                <i class="fas fa-user me-1"></i>{{ $reminder->lead->name }}
                                            </small>
                                            <small class="text-danger fw-medium">
                                                <i class="fas fa-clock me-1"></i>{{ $reminder->reminder_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </a>
                                </li>
                                @endif
                            @endforeach
                        @endif
                        
                        @if($upcomingReminders->count() > 0)
                            <!-- Upcoming Section -->
                            <li class="bg-warning-subtle">
                                <div class="dropdown-header py-1 small fw-semibold text-warning-emphasis">
                                    <i class="fas fa-clock me-1"></i>UPCOMING (24h)
                                </div>
                            </li>
                            @foreach($upcomingReminders as $reminder)
                                @if($reminder->lead)
                                <li>
                                    <a class="dropdown-item d-flex align-items-start py-2 border-bottom" href="{{ route('vendor.leads.show', $reminder->lead_id) }}">
                                        <div class="rounded-circle bg-warning text-dark me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-medium text-truncate">{{ $reminder->title }}</div>
                                            <small class="text-secondary d-block text-truncate">
                                                <i class="fas fa-user me-1"></i>{{ $reminder->lead->name }}
                                            </small>
                                            <small class="text-warning-emphasis">
                                                <i class="fas fa-calendar me-1"></i>{{ $reminder->reminder_at->format('M d, h:i A') }}
                                            </small>
                                        </div>
                                    </a>
                                </li>
                                @endif
                            @endforeach
                        @endif
                        
                        @if($dueReminders->count() == 0 && $upcomingReminders->count() == 0)
                            <li>
                                <div class="dropdown-item d-flex align-items-center py-4">
                                    <div class="text-center w-100">
                                        <i class="fas fa-check-circle text-success fa-3x mb-2"></i>
                                        <div class="fw-medium">No pending reminders</div>
                                        <small class="text-secondary">You're all caught up!</small>
                                    </div>
                                </div>
                            </li>
                        @endif
                        
                        <!-- Footer -->
                        <li class="bg-light border-top sticky-bottom">
                            <a class="dropdown-item text-center fw-medium text-primary py-2" href="{{ route('vendor.leads.reminders') }}">
                                <i class="fas fa-list me-1"></i>View all reminders
                            </a>
                        </li>
                    </ul>
                </div>
                @endif
                
                <!-- Notifications -->
                <div class="dropdown me-2">
                    <button class="btn btn-outline-secondary position-relative rounded-circle" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2 notification-dropdown" aria-labelledby="notificationsDropdown" style="min-width: 350px;">
                        <li><h6 class="dropdown-header fw-semibold">Notifications</h6></li>
                        
                        @forelse(auth()->user()->notifications->take(5) as $notification)
                            <li class="notification-list-item">
                                <a class="dropdown-item d-flex align-items-start py-2 notification-item {{ $notification->read ? '' : 'bg-light' }}" 
                                   href="#" 
                                   data-notification-id="{{ $notification->id }}" 
                                   data-notification-type="{{ $notification->type }}" 
                                   data-notification-data="{{ json_encode($notification->data) }}">
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        @php
                                            $notificationData = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
                                        @endphp
                                        
                                        @if($notification->type === 'proforma_invoice')
                                            <i class="fas fa-file-invoice text-primary"></i>
                                        @elseif($notification->type === 'without_gst_invoice')
                                            <i class="fas fa-file-alt text-secondary"></i>
                                        @elseif($notification->type === 'lead')
                                            <i class="fas fa-user-plus text-success"></i>
                                        @elseif($notification->type === 'product')
                                            <i class="fas fa-box text-info"></i>
                                        @else
                                            <i class="fas fa-bell text-info"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium">{{ $notification->title ?? 'Notification' }}</div>
                                        <small class="text-secondary">{{ $notification->message ?? '' }}</small>
                                        <br>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if(!$notification->read)
                                        <span class="badge bg-primary rounded-pill ms-2">New</span>
                                    @endif
                                </a>
                            </li>
                        @empty
                            <li class="no-notifications-item">
                                <a class="dropdown-item d-flex align-items-center py-2" href="#">
                                    <div class="text-center w-100">
                                        <div class="fw-medium">No notifications</div>
                                        <small class="text-secondary">You're all caught up</small>
                                    </div>
                                </a>
                            </li>
                        @endforelse
                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center fw-medium text-danger" href="#" id="clearAllNotifications">Clear all notifications</a></li>
                    </ul>
                </div>
                
                <!-- Subscription Status (Vendor Owner Only) -->
                @if(Auth::user()->isVendor() && Auth::user()->vendor)
                    @php
                        $activeSubscription = Auth::user()->vendor->activeSubscription;
                    @endphp
                    @if($activeSubscription)
                        <div class="me-2">
                            <a href="{{ route('vendor.subscription.current') }}" 
                               class="btn btn-sm {{ $activeSubscription->daysRemaining() <= 7 && $activeSubscription->ends_at ? 'btn-warning' : 'btn-outline-success' }}" 
                               title="Subscription: {{ $activeSubscription->plan->name }}">
                                <i class="fas fa-crown me-1"></i>
                                <span class="d-none d-md-inline">{{ $activeSubscription->plan->name }}</span>
                                @if($activeSubscription->ends_at && $activeSubscription->daysRemaining() <= 7)
                                    <span class="badge bg-danger ms-1">{{ $activeSubscription->daysRemaining() }}d</span>
                                @endif
                            </a>
                        </div>
                    @else
                        <div class="me-2">
                            <a href="{{ route('vendor.subscription.plans') }}" 
                               class="btn btn-sm btn-danger" 
                               title="No Active Subscription">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span class="d-none d-md-inline">Subscribe Now</span>
                            </a>
                        </div>
                    @endif
                @endif
                
                <!-- User Profile -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center py-1 px-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img class="rounded-circle me-2" src="{{ Auth::user()->avatar ? asset('storage/avatars/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=random' }}" alt="{{ Auth::user()->name }}" width="32" height="32">
                        <div class="d-none d-md-block text-start">
                            <div class="fw-medium small mb-0">{{ Auth::user()->name }}</div>
                            <small>{{ Auth::user()->user_role === 'vendor_staff' ? 'Staff' : 'Vendor' }}</small>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2" aria-labelledby="userDropdown">
                        <li><h6 class="dropdown-header fw-semibold">{{ Auth::user()->name }}</h6></li>
                        <li><a class="dropdown-item" href="{{ route('vendor.profile.index') }}"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('vendor.profile.store') }}"><i class="fas fa-cog me-2"></i>Store Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('vendor.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationDropdown = document.querySelector('.notification-dropdown');
    const clearAllBtn = document.getElementById('clearAllNotifications');
    const notificationItems = document.querySelectorAll('.notification-item');
    
    // Handle individual notification clicks (navigation + delete notification)
    notificationItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const notificationType = this.getAttribute('data-notification-type');
            const notificationData = this.getAttribute('data-notification-data');
            const notificationId = this.getAttribute('data-notification-id');
            const listItem = this.closest('.notification-list-item');
            
            // Determine redirect URL based on notification type
            let redirectUrl = null;
            
            if (notificationType === 'proforma_invoice') {
                try {
                    const data = JSON.parse(notificationData);
                    if (data.invoice_id) {
                        redirectUrl = `/vendor/invoices/${data.invoice_id}`;
                    }
                } catch (e) {
                    console.error('Error parsing notification data:', e);
                }
            } else if (notificationType === 'without_gst_invoice') {
                try {
                    const data = JSON.parse(notificationData);
                    if (data.invoice_id) {
                        redirectUrl = `/vendor/invoices-black/${data.invoice_id}`;
                    }
                } catch (e) {
                    console.error('Error parsing notification data:', e);
                }
            } else if (notificationType === 'lead') {
                try {
                    const data = JSON.parse(notificationData);
                    if (data.lead_id) {
                        redirectUrl = `/vendor/leads/${data.lead_id}`;
                    }
                } catch (e) {
                    console.error('Error parsing notification data:', e);
                }
            } else if (notificationType === 'product') {
                try {
                    const data = JSON.parse(notificationData);
                    if (data.product_id) {
                        redirectUrl = `/vendor/products/${data.product_id}`;
                    }
                } catch (e) {
                    console.error('Error parsing notification data:', e);
                }
            }
            
            // Delete notification and redirect
            deleteNotification(notificationId, listItem, redirectUrl);
        });
    });
    
    // Clear all notifications (delete them)
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            fetch('/vendor/notifications', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update notification count to 0
                    updateNotificationCount(0);
                    // Remove all notification items from DOM
                    document.querySelectorAll('.notification-list-item').forEach(item => {
                        item.remove();
                    });
                    // Show "no notifications" message
                    showNoNotificationsMessage();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
    
    // Function to delete a specific notification
    function deleteNotification(notificationId, listItem, redirectUrl = null) {
        fetch(`/vendor/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update notification count
                updateNotificationCount(data.unread_count);
                // Remove the notification item from DOM
                if (listItem) {
                    listItem.remove();
                }
                // Check if there are any notifications left
                const remainingItems = document.querySelectorAll('.notification-list-item');
                if (remainingItems.length === 0) {
                    showNoNotificationsMessage();
                }
                
                // Redirect if URL provided
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Still redirect even if deletion fails
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        });
    }
    
    // Function to show "no notifications" message
    function showNoNotificationsMessage() {
        const dropdown = document.querySelector('.notification-dropdown');
        const divider = dropdown.querySelector('.dropdown-divider');
        
        // Check if no-notifications message already exists
        if (!dropdown.querySelector('.no-notifications-item')) {
            const noNotificationsHtml = `
                <li class="no-notifications-item">
                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                        <div class="text-center w-100">
                            <div class="fw-medium">No notifications</div>
                            <small class="text-secondary">You're all caught up</small>
                        </div>
                    </a>
                </li>
            `;
            if (divider) {
                divider.closest('li').insertAdjacentHTML('beforebegin', noNotificationsHtml);
            }
        }
    }
    
    // Function to update notification count
    function updateNotificationCount(count) {
        const countElement = document.querySelector('#notificationsDropdown .notification-count');
        if (count > 0) {
            if (countElement) {
                countElement.textContent = count;
            } else {
                const badge = document.createElement('span');
                badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count';
                badge.textContent = count;
                document.querySelector('#notificationsDropdown').appendChild(badge);
            }
        } else {
            if (countElement) {
                countElement.remove();
            }
        }
    }
});
</script>
