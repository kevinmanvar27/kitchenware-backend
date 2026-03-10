<!-- Vendor Sidebar -->
@php
    use App\Models\VendorFeatureSetting;
    
    $user = Auth::user();
    $isVendorOwner = $user->isVendor();
    $staffPermissions = [];
    $vendor = null;
    
    if ($isVendorOwner) {
        $vendor = $user->vendor;
    } elseif ($user->isVendorStaff()) {
        $staffRecord = $user->vendorStaff;
        $staffPermissions = $staffRecord ? ($staffRecord->permissions ?? []) : [];
        $vendor = $staffRecord ? $staffRecord->vendor : null;
    }
    
    // Helper function to check if user has permission
    $hasPermission = function($permission) use ($isVendorOwner, $staffPermissions) {
        if ($isVendorOwner) return true;
        return in_array($permission, $staffPermissions);
    };
    
    // Helper function to check if feature is enabled for this vendor
    $isFeatureEnabled = function($featureKey) use ($vendor) {
        if (!$vendor) return true; // If no vendor, default to enabled
        return VendorFeatureSetting::isFeatureEnabledForVendor($vendor->id, $featureKey);
    };
    
    // Combined check: has permission AND feature is enabled
    $canAccess = function($permission) use ($hasPermission, $isFeatureEnabled) {
        return $hasPermission($permission) && $isFeatureEnabled($permission);
    };
    
    // Group permission checks for section headers (now includes feature checks)
    $hasCatalogPermission = $canAccess('products') || $canAccess('categories') || $canAccess('attributes');
    $hasSalesPermission = $canAccess('invoices') || $canAccess('pending_bills');
    $hasCustomerPermission = $canAccess('customers') || $canAccess('leads');
    $hasTeamPermission = $canAccess('staff') || $canAccess('salary') || $canAccess('attendance') || $canAccess('view_tasks');
    $hasMarketingPermission = $canAccess('coupons') || $canAccess('analytics') || $canAccess('reports') || $canAccess('push_notifications');
    $hasSettingsPermission = $hasPermission('profile') || $hasPermission('store_settings') || $canAccess('activity_logs');
@endphp

<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-surface sidebar">
    <!-- Mobile close button -->
    <button type="button" class="sidebar-close" id="sidebar-close" aria-label="Close sidebar">
        <i class="fas fa-times"></i>
    </button>
    <div class="position-sticky pt-3 d-flex flex-column vh-100">
        <div class="px-3 pb-3 border-bottom border-default sidebar-header">
            <div class="d-flex align-items-center mb-3">
                @if($vendor && $vendor->store_logo_url)
                    <img src="{{ $vendor->store_logo_url }}" alt="{{ $vendor->store_name ?? 'Vendor' }}" class="me-2 rounded sidebar-logo" height="48">
                @elseif(setting('header_logo'))
                    <img src="{{ asset('storage/' . setting('header_logo')) }}" alt="{{ setting('site_title', 'Vendor Panel') }}" class="me-2 rounded sidebar-logo" height="48">
                @else
                    <div class="bg-theme rounded-circle d-flex align-items-center justify-content-center me-2 sidebar-logo-icon" style="width: 48px; height: 48px;">
                        <i class="fas fa-store text-white"></i>
                    </div>
                @endif
                <div>
                    <h1 class="h5 mb-0 fw-bold sidebar-header-text">{{ $vendor->store_name ?? 'Vendor Panel' }}</h1>
                    @if(!$isVendorOwner && $user->isVendorStaff())
                        <small class="text-muted">{{ $user->vendorStaff->role ?? 'Staff' }}</small>
                    @endif
                </div>
            </div>
        </div>
        
        <ul class="nav flex-column flex-grow-1">
            {{-- ========================================
                1. DASHBOARD - Always First
            ======================================== --}}
            @if($hasPermission('dashboard'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}" href="{{ route('vendor.dashboard') }}" data-title="Dashboard">
                    <i class="fas fa-home me-3"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            @endif
            
            {{-- ========================================
                2. CATALOG MANAGEMENT
                (Products, Categories, Attributes)
            ======================================== --}}
            @if($hasCatalogPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Catalog</span>
                </span>
            </li>
            @endif
            
            @if($canAccess('products'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.products*') ? 'active' : '' }}" href="{{ route('vendor.products.index') }}" data-title="Products">
                    <i class="fas fa-box me-3"></i>
                    <span class="sidebar-text">Products</span>
                </a>
            </li>
            @endif
            
            @if($canAccess('categories'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.categories*') ? 'active' : '' }}" href="{{ route('vendor.categories.index') }}" data-title="Categories">
                    <i class="fas fa-tags me-3"></i>
                    <span class="sidebar-text">Categories</span>
                </a>
            </li>
            @endif
            
            @if($canAccess('attributes'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.attributes*') ? 'active' : '' }}" href="{{ route('vendor.attributes.index') }}" data-title="Attributes">
                    <i class="fas fa-sliders-h me-3"></i>
                    <span class="sidebar-text">Attributes</span>
                </a>
            </li>
            @endif
            
            {{-- ========================================
                3. SALES & ORDERS
                (Invoices, Pending Bills)
            ======================================== --}}
            @if($hasSalesPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Sales</span>
                </span>
            </li>
            @endif
            
            @if($canAccess('invoices'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.invoices.*') && !request()->routeIs('vendor.invoices-black*') ? 'active' : '' }}" href="{{ route('vendor.invoices.index') }}" data-title="Invoices">
                    <i class="fas fa-file-invoice me-3"></i>
                    <span class="sidebar-text">Invoices</span>
                </a>
            </li>
            @endif
            
            @if($canAccess('pending_bills'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.pending-bills*') ? 'active' : '' }}" href="{{ route('vendor.pending-bills.index') }}" data-title="Pending Bills">
                    <i class="fas fa-file-invoice-dollar me-3"></i>
                    <span class="sidebar-text">Pending Bills</span>
                </a>
            </li>
            @endif
            
            {{-- ========================================
                4. CUSTOMER MANAGEMENT
                (Customers, Leads)
            ======================================== --}}
            @if($hasCustomerPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Customers</span>
                </span>
            </li>
            @endif
            
            @if($canAccess('customers'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.customers*') ? 'active' : '' }}" href="{{ route('vendor.customers.index') }}" data-title="Customers">
                    <i class="fas fa-user-friends me-3"></i>
                    <span class="sidebar-text">Customers</span>
                </a>
            </li>
            @endif
            
            @if($canAccess('leads'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.leads*') ? 'active' : '' }}" href="{{ route('vendor.leads.index') }}" data-title="Leads">
                    <i class="fas fa-user-plus me-3"></i>
                    <span class="sidebar-text">Leads</span>
                </a>
            </li>
            @endif
            
            {{-- ========================================
                5. TEAM MANAGEMENT
                (Staff, Attendance, Salary)
            ======================================== --}}
            @if($hasTeamPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Team</span>
                </span>
            </li>
            @endif
            
            @if($canAccess('staff'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.staff*') ? 'active' : '' }}" href="{{ route('vendor.staff.index') }}" data-title="Staff">
                    <i class="fas fa-users me-3"></i>
                    <span class="sidebar-text">Staff</span>
                </a>
            </li>
            @endif
            
            @if($canAccess('attendance'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.attendance*') ? 'active' : '' }}" href="{{ route('vendor.attendance.index') }}" data-title="Attendance">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span class="sidebar-text">Attendance</span>
                </a>
            </li>
            @endif
            
            @if($canAccess('salary'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.salary*') ? 'active' : '' }}" href="{{ route('vendor.salary.index') }}" data-title="Salary">
                    <i class="fas fa-money-bill-wave me-3"></i>
                    <span class="sidebar-text">Salary</span>
                </a>
            </li>
            @endif
            
            @if($canAccess('view_tasks'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.tasks*') ? 'active' : '' }}" href="{{ route('vendor.tasks.index') }}" data-title="Task Management">
                    <i class="fas fa-tasks me-3"></i>
                    <span class="sidebar-text">Task Management</span>
                </a>
            </li>
            @endif
            
            {{-- ========================================
                6. MARKETING & ANALYTICS
                (Coupons, Reports, Analytics)
            ======================================== --}}
            @if($hasMarketingPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Marketing</span>
                </span>
            </li>
            @endif
            
            @if($canAccess('coupons'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.coupons*') ? 'active' : '' }}" href="{{ route('vendor.coupons.index') }}" data-title="Coupons">
                    <i class="fas fa-ticket-alt me-3"></i>
                    <span class="sidebar-text">Coupons</span>
                </a>
            </li>
            @endif
            
            @if($canAccess('reports'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.reports*') ? 'active' : '' }}" href="{{ route('vendor.reports.index') }}" data-title="Reports">
                    <i class="fas fa-chart-bar me-3"></i>
                    <span class="sidebar-text">Reports</span>
                </a>
            </li>
            @endif
            
            @if($canAccess('analytics'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.analytics*') ? 'active' : '' }}" href="{{ route('vendor.analytics.products') }}" data-title="Analytics">
                    <i class="fas fa-chart-line me-3"></i>
                    <span class="sidebar-text">Analytics</span>
                </a>
            </li>
            @endif
            
            @if($canAccess('push_notifications'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.push-notifications*') ? 'active' : '' }}" href="{{ route('vendor.push-notifications.index') }}" data-title="Push Notifications">
                    <i class="fas fa-bell me-3"></i>
                    <span class="sidebar-text">Push Notifications</span>
                </a>
            </li>
            @endif
            
            {{-- ========================================
                7. SUBSCRIPTION & REFERRALS
                (Only for Vendor Owner)
            ======================================== --}}
            @if($isVendorOwner)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Account</span>
                </span>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.subscription*') ? 'active' : '' }}" href="{{ route('vendor.subscription.current') }}" data-title="Subscription">
                    <i class="fas fa-crown me-3"></i>
                    <span class="sidebar-text">Subscription</span>
                    @if($vendor->activeSubscription && $vendor->activeSubscription->ends_at && $vendor->activeSubscription->daysRemaining() <= 7)
                        <span class="badge bg-warning ms-2">{{ $vendor->activeSubscription->daysRemaining() }}d</span>
                    @endif
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.referral.my-code') ? 'active' : '' }}" href="{{ route('vendor.referral.my-code') }}" data-title="My Referral Code">
                    <i class="fas fa-gift me-3"></i>
                    <span class="sidebar-text">Referral Code</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.referral.earnings') ? 'active' : '' }}" href="{{ route('vendor.referral.earnings') }}" data-title="Referral Earnings">
                    <i class="fas fa-money-bill-wave me-3"></i>
                    <span class="sidebar-text">Referral Earnings</span>
                    @if($vendor && ($vendor->pending_referral_earnings > 0 || $vendor->approved_referral_earnings > 0))
                        <span class="badge bg-success ms-2">₹{{ number_format($vendor->pending_referral_earnings + $vendor->approved_referral_earnings, 0) }}</span>
                    @endif
                </a>
            </li>
            @endif
            
            {{-- ========================================
                8. SETTINGS - Always Last
                (Profile, Store Settings)
            ======================================== --}}
            @if($hasSettingsPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Settings</span>
                </span>
            </li>
            @endif
            
            @if($hasPermission('profile'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.profile.index') ? 'active' : '' }}" href="{{ route('vendor.profile.index') }}" data-title="Profile">
                    <i class="fas fa-user me-3"></i>
                    <span class="sidebar-text">Profile</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.profile.bank-details') ? 'active' : '' }}" href="{{ route('vendor.profile.bank-details') }}" data-title="Bank Details">
                    <i class="fas fa-university me-3"></i>
                    <span class="sidebar-text">Bank Details</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission('store_settings'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.profile.store') ? 'active' : '' }}" href="{{ route('vendor.profile.store') }}" data-title="Store Settings">
                    <i class="fas fa-store me-3"></i>
                    <span class="sidebar-text">Store Settings</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission('banners'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.banners*') ? 'active' : '' }}" href="{{ route('vendor.banners.index') }}" data-title="Banners">
                    <i class="fas fa-images me-3"></i>
                    <span class="sidebar-text">Banners</span>
                </a>
            </li>
            @endif
            
            @if($isVendorOwner)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.feature-settings*') ? 'active' : '' }}" href="{{ route('vendor.feature-settings.index') }}" data-title="Feature Settings">
                    <i class="fas fa-toggle-on me-3"></i>
                    <span class="sidebar-text">Feature Settings</span>
                </a>
            </li>
            @endif
            
            @if($canAccess('activity_logs'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vendor.activity-logs*') ? 'active' : '' }}" href="{{ route('vendor.activity-logs.index') }}" data-title="Activity Logs">
                    <i class="fas fa-history me-3"></i>
                    <span class="sidebar-text">Activity Logs</span>
                </a>
            </li>
            @endif
        </ul>
        
        <div class="px-3 py-3 border-top border-default mt-auto sidebar-footer">
            <div class="d-flex align-items-center mb-3 sidebar-status">
                @php
                    $vendorStatus = $vendor->status ?? 'pending';
                    $statusColor = match($vendorStatus) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'suspended' => 'danger',
                        'rejected' => 'danger',
                        default => 'secondary'
                    };
                @endphp
                <div class="bg-{{ $statusColor }} rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                <div class="small sidebar-text">
                    @if($isVendorOwner)
                        <span class="text-secondary">Store {{ ucfirst($vendorStatus) }}</span>
                    @else
                        <span class="text-secondary">Staff - {{ ucfirst($user->vendorStaff->role ?? 'Member') }}</span>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('vendor.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 rounded py-2" data-title="Logout">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    <span class="sidebar-text">Logout</span>
                </button>
            </form>
        </div>
    </div>
</nav>
