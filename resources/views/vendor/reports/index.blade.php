@extends('vendor.layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Reports & Analytics'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Date Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('vendor.reports.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small text-muted">From Date</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">To Date</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                            </div>
                            <div class="col-md-6 d-flex gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-filter me-1"></i> Apply Filter
                                </button>
                                <a href="{{ route('vendor.reports.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-outline-success rounded-pill px-4">
                                    <i class="fas fa-download me-1"></i> Export Report
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 bg-success rounded-3 p-3">
                                        <i class="fas fa-rupee-sign text-white fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1 small">Total Revenue</h6>
                                        <h4 class="mb-0 fw-bold">₹{{ number_format($stats['total_revenue'], 0) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 bg-primary rounded-3 p-3">
                                        <i class="fas fa-wallet text-white fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1 small">Net Earnings</h6>
                                        <h4 class="mb-0 fw-bold">₹{{ number_format($stats['net_earnings'], 0) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 bg-warning rounded-3 p-3">
                                        <i class="fas fa-shopping-cart text-white fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1 small">Total Orders</h6>
                                        <h4 class="mb-0 fw-bold">{{ number_format($stats['total_orders']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 bg-info rounded-3 p-3">
                                        <i class="fas fa-box text-white fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1 small">Products Sold</h6>
                                        <h4 class="mb-0 fw-bold">{{ number_format($stats['total_products_sold']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secondary Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Commission ({{ $stats['commission_rate'] }}%)</h6>
                                <h5 class="text-danger mb-0">₹{{ number_format($stats['total_commission'], 2) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Avg Order Value</h6>
                                <h5 class="text-primary mb-0">₹{{ number_format($stats['avg_order_value'], 2) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Delivered Orders</h6>
                                <h5 class="text-success mb-0">{{ $stats['delivered_orders'] }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Pending Orders</h6>
                                <h5 class="text-warning mb-0">{{ $stats['pending_orders'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Revenue Chart -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-chart-line text-primary me-2"></i>Revenue Trend
                                </h5>
                            </div>
                            <div class="card-body">
                                <div style="height: 300px;">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Chart -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-chart-pie text-primary me-2"></i>Order Status
                                </h5>
                            </div>
                            <div class="card-body">
                                <div style="height: 250px;">
                                    <canvas id="orderStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-2">
                    <!-- Top Products -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-trophy text-warning me-2"></i>Top Selling Products
                                </h5>
                            </div>
                            <div class="card-body">
                                @if(count($topProducts) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Product</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-end">Revenue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topProducts as $index => $product)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($product['image'])
                                                                <img src="{{ asset('storage/' . $product['image']) }}" alt="{{ $product['name'] }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                            @else
                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-image text-muted small"></i>
                                                                </div>
                                                            @endif
                                                            <span class="text-truncate" style="max-width: 150px;">{{ $product['name'] }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">{{ $product['quantity'] }}</td>
                                                    <td class="text-end fw-medium">₹{{ number_format($product['revenue'], 0) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No sales data available</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-tags text-info me-2"></i>Sales by Category
                                </h5>
                            </div>
                            <div class="card-body">
                                @if(count($categoryBreakdown) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Category</th>
                                                    <th class="text-center">Items Sold</th>
                                                    <th class="text-end">Revenue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($categoryBreakdown as $index => $category)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $category['name'] }}</td>
                                                    <td class="text-center">{{ $category['quantity'] }}</td>
                                                    <td class="text-end fw-medium">₹{{ number_format($category['revenue'], 0) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-tags fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No category data available</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueData = @json($revenueChartData);
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(d => d.label),
            datasets: [{
                label: 'Revenue (₹)',
                data: revenueData.map(d => d.value),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
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
                legend: {
                    display: false
                }
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

    // Order Status Chart
    const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(orderStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Delivered', 'Pending', 'Returned'],
            datasets: [{
                data: [{{ $stats['delivered_orders'] }}, {{ $stats['pending_orders'] }}, {{ $stats['returned_orders'] }}],
                backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endsection
