@extends('vendor.layouts.app')

@section('title', 'Dashboard')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .progress-thin {
        height: 6px;
    }
</style>
@endsection

@section('content')
@php
    $user = Auth::user();
    // Define permission checks for vendor dashboard sections
    $canViewOrders = $user->hasVendorPermission('invoices');
    $canViewProducts = $user->hasVendorPermission('products');
    $canViewCategories = $user->hasVendorPermission('categories');
    $canViewLeads = $user->hasVendorPermission('leads');
    $canViewReports = $user->hasVendorPermission('reports');
    $canViewAnalytics = $user->hasVendorPermission('analytics');
    $canViewCustomers = $user->hasVendorPermission('customers');
    $canViewCoupons = $user->hasVendorPermission('coupons');
    
    // Check if user has any dashboard section permission
    $hasAnyDashboardPermission = $canViewOrders || $canViewProducts || $canViewCategories || $canViewLeads || $canViewReports || $canViewAnalytics;
    
    // Prepare stats array for easier access in the template
    $stats = [
        'totalRevenue' => $totalRevenue ?? 0,
        'monthlyRevenue' => $monthlyRevenue ?? 0,
        'totalOrders' => $totalOrders ?? 0,
        'pendingOrders' => $pendingOrders ?? 0,
        'completedOrders' => $deliveredOrders ?? 0,
        'totalCategories' => $categoryCount ?? 0,
    ];
@endphp
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Dashboard'])
            
            @section('page-title', 'Dashboard')
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Vendor Status Alert -->
                @if($vendor->status === 'pending')
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-clock me-3 fa-lg"></i>
                        <div class="flex-grow-1">
                            <strong>Account Pending Approval</strong>
                            <p class="mb-0 small">Your vendor account is currently under review. You'll be notified once it's approved.</p>
                        </div>
                    </div>
                @elseif($vendor->status === 'suspended')
                    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-ban me-3 fa-lg"></i>
                        <div class="flex-grow-1">
                            <strong>Account Suspended</strong>
                            <p class="mb-0 small">Your vendor account has been suspended. Please contact support for assistance.</p>
                        </div>
                    </div>
                @endif
                
                <!-- Welcome Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-6">
                                <h2 class="card-title mb-2 h4">Welcome back, {{ Auth::user()->name }}!</h2>
                                <p class="text-secondary mb-3 small">Here's what's happening with your store today.</p>
                                @if($canViewOrders)
                                <div class="d-flex flex-wrap gap-3 gap-md-4">
                                    <div class="text-center text-md-start">
                                        <span class="text-secondary small d-block">Today's Orders</span>
                                        <h4 class="mb-0 text-primary h5">{{ $todayOrders ?? 0 }}</h4>
                                    </div>
                                    <div class="text-center text-md-start">
                                        <span class="text-secondary small d-block">Today's Revenue</span>
                                        <h4 class="mb-0 text-success h5">₹{{ number_format($todayRevenue ?? 0, 2) }}</h4>
                                    </div>
                                    <div class="text-center text-md-start">
                                        <span class="text-secondary small d-block">Pending Orders</span>
                                        <h4 class="mb-0 text-warning h5">{{ $pendingOrders ?? 0 }}</h4>
                                    </div>
                                </div>
                                @endif
                            </div>
                            {{-- Product buttons removed - products module disabled for vendor panel --}}
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                @if($canViewOrders || $canViewCategories)
                <div class="row g-3 g-md-4 mb-4">
                    @if($canViewOrders)
                    <div class="col-6 col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100 stat-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="stat-icon bg-success rounded-circle" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <i class="fas fa-indian-rupee-sign text-white"></i>
                                    </div>
                                </div>
                                <h3 class="h6 text-secondary mb-1">Total Revenue</h3>
                                <p class="h5 mb-0 fw-bold">₹{{ number_format($stats['totalRevenue'] ?? 0, 2) }}</p>
                                <small class="text-muted">This month: ₹{{ number_format($stats['monthlyRevenue'] ?? 0, 2) }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100 stat-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="stat-icon bg-primary rounded-circle" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <i class="fas fa-shopping-cart text-white"></i>
                                    </div>
                                    <span class="badge bg-primary text-white" style="font-size: 0.65rem;">
                                        {{ $stats['pendingOrders'] ?? 0 }} pending
                                    </span>
                                </div>
                                <h3 class="h6 text-secondary mb-1">Total Orders</h3>
                                <p class="h5 mb-0 fw-bold">{{ $stats['totalOrders'] ?? 0 }}</p>
                                <small class="text-muted">Completed: {{ $stats['completedOrders'] ?? 0 }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    {{-- Products stats card removed - products module disabled for vendor panel --}}
                    
                    @if($canViewCategories)
                    <div class="col-6 col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100 stat-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="stat-icon bg-info rounded-circle" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <i class="fas fa-tags text-white"></i>
                                    </div>
                                </div>
                                <h3 class="h6 text-secondary mb-1">Categories</h3>
                                <p class="h5 mb-0 fw-bold">{{ $stats['totalCategories'] ?? 0 }}</p>
                                <small class="text-muted">With products</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Total Earnings Card -->
                    <div class="col-6 col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100 stat-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="stat-icon bg-success rounded-circle" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <i class="fas fa-wallet text-white"></i>
                                    </div>
                                    @if(isset($walletBalance['pending_amount']) && $walletBalance['pending_amount'] > 0)
                                    <span class="badge bg-warning text-white" style="font-size: 0.65rem;">
                                        ₹{{ number_format($walletBalance['pending_amount'], 2) }} pending
                                    </span>
                                    @endif
                                </div>
                                <h3 class="h6 text-secondary mb-1">Total Earnings</h3>
                                <p class="h5 mb-0 fw-bold">₹{{ number_format($netEarnings ?? 0, 2) }}</p>
                                <small class="text-muted">Paid: ₹{{ number_format($walletBalance['total_paid'] ?? 0, 2) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Charts Row -->
                @if($canViewOrders)
                <div class="row g-3 g-md-4 mb-4">
                    <!-- Revenue Chart -->
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0 fw-semibold">Revenue Overview</h3>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-secondary active" id="weeklyBtn" onclick="switchChart('weekly')">Weekly</button>
                                    <button type="button" class="btn btn-outline-secondary" id="monthlyBtn" onclick="switchChart('monthly')">Monthly</button>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Status -->
                    <div class="col-12 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0">
                                <h3 class="h6 mb-0 fw-semibold">Order Status</h3>
                            </div>
                            <div class="card-body p-3">
                                <div class="chart-container" style="height: 180px;">
                                    <canvas id="orderStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Recent Orders -->
                @if($canViewOrders)
                <div class="row g-3 g-md-4 mb-4">
                    <!-- Recent Orders -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0 fw-semibold">Recent Orders</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0 ps-3">Order</th>
                                                <th class="border-0">Customer</th>
                                                <th class="border-0">Amount</th>
                                                <th class="border-0">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentOrders ?? [] as $order)
                                            <tr>
                                                <td class="ps-3">
                                                    <span class="text-decoration-none fw-medium small">
                                                        #{{ $order['id'] ?? 'N/A' }}
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">{{ isset($order['created_at']) ? \Carbon\Carbon::parse($order['created_at'])->format('M d, Y') : 'N/A' }}</small>
                                                </td>
                                                <td class="small">{{ $order['user']->name ?? 'Guest' }}</td>
                                                <td class="fw-medium small">₹{{ number_format($order['vendor_total'] ?? 0, 2) }}</td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'draft' => 'secondary',
                                                            'approved' => 'info',
                                                            'dispatch' => 'primary',
                                                            'out_for_delivery' => 'warning',
                                                            'delivered' => 'success',
                                                            'return' => 'danger'
                                                        ];
                                                        $color = $statusColors[$order['status'] ?? ''] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $color }}" style="font-size: 0.65rem;">{{ ucfirst(str_replace('_', ' ', $order['status'] ?? 'Unknown')) }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                    No orders yet
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Top Products section removed - products module disabled for vendor panel --}}
                </div>
                @endif
                
                <!-- Earnings Summary -->
                <div class="row g-3 g-md-4 mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0 fw-semibold">Earnings Summary</h3>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-secondary">Total Earned:</span>
                                            <span class="fw-bold">₹{{ number_format($walletBalance['total_earned'] ?? 0, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-secondary">Total Paid:</span>
                                            <span class="fw-bold text-success">₹{{ number_format($walletBalance['total_paid'] ?? 0, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-secondary">Pending Amount:</span>
                                            <span class="fw-bold text-warning">₹{{ number_format($walletBalance['pending_amount'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-secondary mb-2">Earnings Breakdown</h6>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small>Pending</small>
                                                <small>₹{{ number_format($walletBalance['earnings_breakdown']['pending'] ?? 0, 2) }}</small>
                                            </div>
                                            <div class="progress progress-thin">
                                                <div class="progress-bar bg-warning" role="progressbar" 
                                                    style="width: {{ $netEarnings > 0 ? (($walletBalance['earnings_breakdown']['pending'] ?? 0) / $netEarnings) * 100 : 0 }}%" 
                                                    aria-valuenow="{{ $walletBalance['earnings_breakdown']['pending'] ?? 0 }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="{{ $netEarnings }}"></div>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small>Confirmed</small>
                                                <small>₹{{ number_format($walletBalance['earnings_breakdown']['confirmed'] ?? 0, 2) }}</small>
                                            </div>
                                            <div class="progress progress-thin">
                                                <div class="progress-bar bg-info" role="progressbar" 
                                                    style="width: {{ $netEarnings > 0 ? (($walletBalance['earnings_breakdown']['confirmed'] ?? 0) / $netEarnings) * 100 : 0 }}%" 
                                                    aria-valuenow="{{ $walletBalance['earnings_breakdown']['confirmed'] ?? 0 }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="{{ $netEarnings }}"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small>Paid</small>
                                                <small>₹{{ number_format($walletBalance['earnings_breakdown']['paid'] ?? 0, 2) }}</small>
                                            </div>
                                            <div class="progress progress-thin">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                    style="width: {{ $netEarnings > 0 ? (($walletBalance['earnings_breakdown']['paid'] ?? 0) / $netEarnings) * 100 : 0 }}%" 
                                                    aria-valuenow="{{ $walletBalance['earnings_breakdown']['paid'] ?? 0 }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="{{ $netEarnings }}"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- No Permissions Fallback -->
                @if(!$hasAnyDashboardPermission)
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Limited Access</h5>
                        <p class="text-secondary mb-0">You don't have permission to view dashboard statistics. Please contact your administrator for access.</p>
                    </div>
                </div>
                @endif
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('scripts')
@if(Auth::user()->hasVendorPermission('invoices'))
<script>
    // Chart.js configuration
    const weeklyData = @json($weeklyRevenueData ?? []);
    const monthlyData = @json($monthlyRevenueData ?? []);
    const orderStatusData = @json($orderStatusData ?? []);
    
    let revenueChart;
    let currentView = 'weekly';
    
    // Initialize Revenue Chart
    function initRevenueChart(data, isWeekly = true) {
        const chartElement = document.getElementById('revenueChart');
        if (!chartElement) return;
        
        const ctx = chartElement.getContext('2d');
        
        if (revenueChart) {
            revenueChart.destroy();
        }
        
        const labels = isWeekly ? data.map(d => d.day) : data.map(d => d.month);
        const revenues = data.map(d => d.revenue);
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(13, 110, 253, 0.3)');
        gradient.addColorStop(1, 'rgba(13, 110, 253, 0.01)');
        
        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: revenues,
                    borderColor: '#0d6efd',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Initialize Order Status Chart
    function initOrderStatusChart() {
        const chartElement = document.getElementById('orderStatusChart');
        if (!chartElement || !orderStatusData.length) return;
        
        const ctx = chartElement.getContext('2d');
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: orderStatusData.map(d => d.status),
                datasets: [{
                    data: orderStatusData.map(d => d.count),
                    backgroundColor: orderStatusData.map(d => d.color),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 10 }
                    }
                },
                cutout: '60%'
            }
        });
    }
    
    function switchChart(view) {
        currentView = view;
        document.getElementById('weeklyBtn').classList.toggle('active', view === 'weekly');
        document.getElementById('monthlyBtn').classList.toggle('active', view === 'monthly');
        initRevenueChart(view === 'weekly' ? weeklyData : monthlyData, view === 'weekly');
    }
    
    // Initialize charts on page load
    document.addEventListener('DOMContentLoaded', function() {
        initRevenueChart(weeklyData, true);
        initOrderStatusChart();
    });
</script>
@endif
@endsection
