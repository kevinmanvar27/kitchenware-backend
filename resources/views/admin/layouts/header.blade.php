<!-- Header -->
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
                    <h1 class="h4 mb-0 fw-semibold">{{ $pageTitle ?? 'Dashboard' }}</h1>
                    <!-- Breadcrumbs -->
                    @if (isset($breadcrumbs) && is_array($breadcrumbs))
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
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
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2 notification-dropdown" aria-labelledby="notificationsDropdown" style="min-width: 320px; max-width: 400px; max-height: 400px; overflow-y: auto;">
                        <li><h6 class="dropdown-header fw-semibold">Notifications</h6></li>
                        
                        @forelse(auth()->user()->notifications->take(5) as $notification)
                            <li>
                                <a class="dropdown-item d-flex align-items-start py-2 notification-item" href="#" data-notification-id="{{ $notification->id }}" data-notification-type="{{ $notification->type }}" data-notification-data="{{ json_encode($notification->data) }}">
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        @php
                                            $notificationData = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
                                            $hasAvatar = isset($notificationData['customer_avatar']) && !empty($notificationData['customer_avatar']);
                                        @endphp
                                        
                                        @if($hasAvatar)
                                            <img src="{{ $notificationData['customer_avatar'] }}" alt="User Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            @if($notification->type === 'proforma_invoice')
                                                <i class="fas fa-file-invoice text-primary"></i>
                                            @elseif($notification->type === 'user_registered')
                                                <i class="fas fa-user-plus text-primary"></i>
                                            @elseif($notification->type === 'settings_updated')
                                                <i class="fas fa-cog text-success"></i>
                                            @else
                                                <i class="fas fa-bell text-info"></i>
                                            @endif
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $notification->title }}</div>
                                        <small class="text-secondary">{{ $notification->message }}</small>
                                        <br>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li>
                                <a class="dropdown-item d-flex align-items-center py-2" href="#">
                                    <div class="text-center w-100">
                                        <div class="fw-medium">No notifications</div>
                                        <small class="text-secondary">You're all caught up</small>
                                    </div>
                                </a>
                            </li>
                        @endforelse
                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center fw-medium" href="#" id="markAllAsRead">Mark all as read</a></li>
                    </ul>
                </div>
                
                <!-- User Profile -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center py-1 px-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img class="rounded-circle me-2" src="{{ Auth::user()->avatar ? asset('storage/avatars/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=random' }}" alt="{{ Auth::user()->name }}" width="32" height="32">
                        <div class="d-none d-md-block text-start">
                            <div class="fw-medium small mb-0">{{ Auth::user()->name }}</div>
                            <small>{{ ucfirst(str_replace('_', ' ', Auth::user()->user_role)) }}</small>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2" aria-labelledby="userDropdown">
                        <li><h6 class="dropdown-header fw-semibold">{{ Auth::user()->name }}</h6></li>
                        <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings') }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
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
    const markAllAsReadBtn = document.getElementById('markAllAsRead');
    const notificationItems = document.querySelectorAll('.notification-item');
    
    // Remove the automatic removal when dropdown is shown
    // This addresses requirement #1: Don't automatically remove notifications when opening dropdown
    
    // Handle individual notification clicks (navigation + removal)
    notificationItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Get notification data
            const notificationType = this.getAttribute('data-notification-type');
            const notificationData = this.getAttribute('data-notification-data');
            const notificationId = this.getAttribute('data-notification-id');
            const listItem = this.parentElement; // Get the parent li element
            
            // First navigate to the relevant page (requirement #2)
            if (notificationType === 'proforma_invoice') {
                try {
                    const data = JSON.parse(notificationData);
                    if (data.invoice_id) {
                        // Navigate to the proforma invoice page
                        window.location.href = `/admin/proforma-invoice/${data.invoice_id}`;
                        // Remove the notification after navigation
                        removeNotification(notificationId, listItem);
                        return;
                    }
                } catch (e) {
                    console.error('Error parsing notification data:', e);
                }
            }
            
            // For other notification types, just remove the notification
            e.preventDefault();
            removeNotification(notificationId, listItem);
        });
    });
    
    // Mark all notifications as read (remove all)
    markAllAsReadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
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
                // Update notification count
                updateNotificationCount(data.unread_count);
                // Remove all notification items from the dropdown
                notificationItems.forEach(item => {
                    item.parentElement.remove();
                });
                // Show the "No notifications" message
                showNoNotificationsMessage();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
    
    // Function to remove a specific notification
    function removeNotification(notificationId, listItem) {
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
                // Update notification count
                updateNotificationCount(data.unread_count);
                // Remove the notification item from the dropdown
                listItem.remove();
                // Check if there are no more notifications and show the "No notifications" message
                checkAndShowNoNotificationsMessage();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // Function to update notification count
    function updateNotificationCount(count) {
        const countElement = document.querySelector('#notificationsDropdown .notification-count');
        if (count > 0) {
            if (countElement) {
                countElement.textContent = count;
            } else {
                // Create count element if it doesn't exist
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
    
    // Function to check if there are no notifications and show the "No notifications" message
    function checkAndShowNoNotificationsMessage() {
        const notificationList = document.querySelector('.notification-dropdown');
        const notificationItems = notificationList.querySelectorAll('.notification-item');
        
        if (notificationItems.length === 0) {
            showNoNotificationsMessage();
        }
    }
    
    // Function to show the "No notifications" message
    function showNoNotificationsMessage() {
        const notificationList = document.querySelector('.notification-dropdown');
        const noNotificationsItem = document.createElement('li');
        noNotificationsItem.innerHTML = `
            <a class="dropdown-item d-flex align-items-center py-2" href="#">
                <div class="text-center w-100">
                    <div class="fw-medium">No notifications</div>
                    <small class="text-secondary">You're all caught up</small>
                </div>
            </a>
        `;
        // Insert after the dropdown header
        const header = notificationList.querySelector('.dropdown-header');
        if (header) {
            header.nextElementSibling.insertAdjacentElement('afterend', noNotificationsItem);
        }
    }
});
</script>