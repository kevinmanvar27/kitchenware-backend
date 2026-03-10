<!-- Sidebar -->
@php
    $user = auth()->user();
    
    // Helper function to check permissions
    $hasPermission = function($permissions) use ($user) {
        if ($user->isSuperAdmin()) return true;
        foreach ((array)$permissions as $permission) {
            if ($user->hasPermission($permission)) return true;
        }
        return false;
    };
    
    // Group permission checks for section headers
    $hasCatalogPermission = $hasPermission(['viewAny_product', 'create_product', 'update_product', 'delete_product', 'viewAny_category', 'create_category', 'update_category', 'delete_category', 'viewAny_product_analytics']);
    $hasSalesPermission = $hasPermission(['manage_proforma_invoices', 'manage_pending_bills']);
    $hasMarketingPermission = $hasPermission(['viewAny_coupon', 'viewAny_referral']);
    $hasCustomerPermission = $hasPermission(['show_user', 'add_user', 'edit_user', 'delete_user', 'viewAny_lead', 'create_lead', 'update_lead', 'delete_lead']);
    $hasTeamPermission = $hasPermission(['show_staff', 'add_staff', 'edit_staff', 'delete_staff', 'viewAny_vendor', 'viewAny_attendance', 'create_attendance', 'update_attendance', 'delete_attendance', 'viewAny_salary', 'create_salary', 'update_salary', 'delete_salary', 'manage_tasks']);
    $hasContentPermission = $hasPermission(['viewAny_page', 'create_page', 'update_page', 'delete_page']) || true;
    $hasSettingsPermission = $hasPermission(['manage_roles', 'manage_settings', 'viewAny_activity_log']);
@endphp

<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-surface sidebar">
    <!-- Mobile close button -->
    <button type="button" class="sidebar-close" id="sidebar-close" aria-label="Close sidebar">
        <i class="fas fa-times"></i>
    </button>
    <div class="position-sticky pt-3 d-flex flex-column vh-100">
        <div class="px-3 pb-3 border-bottom border-default sidebar-header">
            <div class="d-flex align-items-center mb-3">
                @if(setting('header_logo'))
                    <img src="{{ asset('storage/' . setting('header_logo')) }}" alt="{{ setting('site_title', 'Admin Panel') }}" class="me-2 rounded sidebar-logo" height="48">
                @else
                    <div class="bg-theme rounded-circle d-flex align-items-center justify-content-center me-2 sidebar-logo-icon" style="width: 48px; height: 48px;">
                        <i class="fas fa-cube text-white"></i>
                    </div>
                    <h1 class="h5 mb-0 fw-bold sidebar-header-text">{{ setting('site_title', 'Admin Panel') }}</h1>
                @endif
            </div>
        </div>
        
        <ul class="nav flex-column flex-grow-1">
            {{-- ========================================
                1. DASHBOARD - Always First
            ======================================== --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" data-title="Dashboard">
                    <i class="fas fa-home me-3"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            
            {{-- ========================================
                2. CATALOG MANAGEMENT
                (Products, Categories, Attributes, Analytics)
            ======================================== --}}
            @if($hasCatalogPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Catalog</span>
                </span>
            </li>
            @endif
            
            @if($hasPermission(['viewAny_product', 'create_product', 'update_product', 'delete_product']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.products*') && !request()->routeIs('admin.attributes*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}" data-title="Products">
                    <i class="fas fa-box me-3"></i>
                    <span class="sidebar-text">Products</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission(['viewAny_category', 'create_category', 'update_category', 'delete_category']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}" data-title="Categories">
                    <i class="fas fa-tags me-3"></i>
                    <span class="sidebar-text">Categories</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission(['viewAny_product', 'create_product', 'update_product', 'delete_product']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.attributes*') ? 'active' : '' }}" href="{{ route('admin.attributes.index') }}" data-title="Attributes">
                    <i class="fas fa-sliders-h me-3"></i>
                    <span class="sidebar-text">Attributes</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission('viewAny_product_analytics'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.analytics.products*') ? 'active' : '' }}" href="{{ route('admin.analytics.products') }}" data-title="Analytics">
                    <i class="fas fa-chart-line me-3"></i>
                    <span class="sidebar-text">Analytics</span>
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
            
            @if($hasPermission('manage_proforma_invoices'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.proforma-invoice*') ? 'active' : '' }}" href="{{ route('admin.proforma-invoice.index') }}" data-title="Invoices">
                    <i class="fas fa-file-invoice me-3"></i>
                    <span class="sidebar-text">Invoices</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission('manage_pending_bills'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.pending-bills*') ? 'active' : '' }}" href="{{ route('admin.pending-bills.index') }}" data-title="Pending Bills">
                    <i class="fas fa-file-invoice-dollar me-3"></i>
                    <span class="sidebar-text">Pending Bills</span>
                </a>
            </li>
            @endif
            
            {{-- ========================================
                4. MARKETING
                (Coupons, Referrals)
            ======================================== --}}
            @if($hasMarketingPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Marketing</span>
                </span>
            </li>
            @endif
            
            @if($hasPermission('viewAny_coupon'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.coupons*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}" data-title="Coupons">
                    <i class="fas fa-ticket-alt me-3"></i>
                    <span class="sidebar-text">Coupons</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission('viewAny_referral'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.referrals*') && !request()->routeIs('admin.referral-earnings*') ? 'active' : '' }}" href="{{ route('admin.referrals.index') }}" data-title="Referrals">
                    <i class="fas fa-user-friends me-3"></i>
                    <span class="sidebar-text">Referrals</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission('viewAny_referral_earning'))
            @php
                $pendingEarningsCount = \App\Models\ReferralEarning::where('status', 'pending')->count();
            @endphp
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.referral-earnings*') ? 'active' : '' }}" href="{{ route('admin.referral-earnings.index') }}" data-title="Referral Earnings">
                    <i class="fas fa-gift me-3"></i>
                    <span class="sidebar-text">Referral Earnings</span>
                    @if($pendingEarningsCount > 0)
                    <span class="badge bg-warning text-dark ms-auto">{{ $pendingEarningsCount }}</span>
                    @endif
                </a>
            </li>
            @endif
            
            {{-- ========================================
                5. CUSTOMER MANAGEMENT
                (Users, User Groups, Leads)
            ======================================== --}}
            @if($hasCustomerPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Customers</span>
                </span>
            </li>
            @endif
            
            @if($hasPermission(['show_user', 'add_user', 'edit_user', 'delete_user']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.index') && !request()->routeIs('admin.users.staff*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" data-title="Users">
                    <i class="fas fa-users me-3"></i>
                    <span class="sidebar-text">Users</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission(['show_user', 'add_user', 'edit_user', 'delete_user']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.user-groups*') ? 'active' : '' }}" href="{{ route('admin.user-groups.index') }}" data-title="User Groups">
                    <i class="fas fa-users-cog me-3"></i>
                    <span class="sidebar-text">User Groups</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission(['viewAny_lead', 'create_lead', 'update_lead', 'delete_lead']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.leads*') ? 'active' : '' }}" href="{{ route('admin.leads.index') }}" data-title="Leads">
                    <i class="fas fa-bullseye me-3"></i>
                    <span class="sidebar-text">Leads</span>
                </a>
            </li>
            @endif
            
            {{-- ========================================
                6. TEAM MANAGEMENT
                (Staff, Vendors, Attendance, Salary)
            ======================================== --}}
            @if($hasTeamPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Team</span>
                </span>
            </li>
            @endif
            
            @if($hasPermission(['show_staff', 'add_staff', 'edit_staff', 'delete_staff']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.staff*') ? 'active' : '' }}" href="{{ route('admin.users.staff') }}" data-title="Staff">
                    <i class="fas fa-user-tie me-3"></i>
                    <span class="sidebar-text">Staff</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission('viewAny_vendor'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.vendors.index') || request()->routeIs('admin.vendors.show') || request()->routeIs('admin.vendors.edit') ? 'active' : '' }}" href="{{ route('admin.vendors.index') }}" data-title="Vendors">
                    <i class="fas fa-store me-3"></i>
                    <span class="sidebar-text">Vendors</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.vendor-payments.index') && !request()->routeIs('admin.vendor-payments.earnings-report') ? 'active' : '' }}" href="{{ route('admin.vendor-payments.index') }}" data-title="Vendor Payments">
                    <i class="fas fa-money-bill-wave me-3"></i>
                    <span class="sidebar-text">Vendor Payments</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.vendor-payments.earnings-report') ? 'active' : '' }}" href="{{ route('admin.vendor-payments.earnings-report') }}" data-title="Earnings & Commission">
                    <i class="fas fa-chart-line me-3"></i>
                    <span class="sidebar-text">Earnings & Commission</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission('manage_tasks'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.tasks*') ? 'active' : '' }}" href="{{ route('admin.tasks.index') }}" data-title="Task Management">
                    <i class="fas fa-tasks me-3"></i>
                    <span class="sidebar-text">Task Management</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission(['viewAny_attendance', 'create_attendance', 'update_attendance', 'delete_attendance']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.attendance*') ? 'active' : '' }}" href="{{ route('admin.attendance.index') }}" data-title="Attendance">
                    <i class="fas fa-calendar-check me-3"></i>
                    <span class="sidebar-text">Attendance</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission(['viewAny_salary', 'create_salary', 'update_salary', 'delete_salary']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.salary*') ? 'active' : '' }}" href="{{ route('admin.salary.index') }}" data-title="Salary">
                    <i class="fas fa-wallet me-3"></i>
                    <span class="sidebar-text">Salary</span>
                </a>
            </li>
            @endif
            
            {{-- ========================================
                7. CONTENT
                (Pages, Media Library, Notifications)
            ======================================== --}}
            @if($hasContentPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Content</span>
                </span>
            </li>
            @endif
            
            @if($hasPermission(['viewAny_page', 'create_page', 'update_page', 'delete_page']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.pages*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}" data-title="Pages">
                    <i class="fas fa-file-alt me-3"></i>
                    <span class="sidebar-text">Pages</span>
                </a>
            </li>
            @endif
            

            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.firebase.notifications*') ? 'active' : '' }}" href="{{ route('admin.firebase.notifications') }}" data-title="Notifications">
                    <i class="fas fa-bell me-3"></i>
                    <span class="sidebar-text">Notifications</span>
                </a>
            </li>
            
            {{-- ========================================
                8. SETTINGS - Always Last
                (Roles & Permissions, Settings)
            ======================================== --}}
            @if($hasSettingsPermission)
            <li class="nav-item sidebar-section-header">
                <span class="nav-link text-muted small text-uppercase fw-semibold py-2">
                    <span class="sidebar-text">Settings</span>
                </span>
            </li>
            @endif
            
            @if($hasPermission('manage_roles'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.roles*') || request()->routeIs('admin.permissions*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}" data-title="Roles & Permissions">
                    <i class="fas fa-user-shield me-3"></i>
                    <span class="sidebar-text">Roles & Permissions</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission('manage_settings'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}" data-title="Settings">
                    <i class="fas fa-cog me-3"></i>
                    <span class="sidebar-text">Settings</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission('manage_database'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.database*') ? 'active' : '' }}" href="{{ route('admin.database.export.index') }}" data-title="Database Export/Import">
                    <i class="fas fa-database me-3"></i>
                    <span class="sidebar-text">Database Export/Import</span>
                </a>
            </li>
            @endif
            
            @if($hasPermission('viewAny_activity_log'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.activity-logs*') ? 'active' : '' }}" href="{{ route('admin.activity-logs.index') }}" data-title="Activity Logs">
                    <i class="fas fa-history me-3"></i>
                    <span class="sidebar-text">Activity Logs</span>
                </a>
            </li>
            @endif
        </ul>
            
            <div class="px-3 py-3 border-top border-default mt-auto sidebar-footer">
                <div class="d-flex align-items-center mb-3 sidebar-status">
                    <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                    <div class="small sidebar-text">
                        <span class="text-secondary">System Online</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 rounded py-2" data-title="Logout">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        <span class="sidebar-text">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>