@extends('admin.layouts.app')

@section('title', 'Analytics: ' . $product->name . ' - ' . config('app.name', 'Laravel'))

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
    .chart-container-sm {
        position: relative;
        height: 200px;
    }
    .product-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }
    .change-badge {
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Product Analytics'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Back Button & Product Info -->
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('admin.analytics.products') }}" class="btn btn-outline-secondary me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        @if($product->mainPhoto)
                            <img src="{{ $product->mainPhoto->url }}" alt="{{ $product->name }}" class="product-image me-3">
                        @else
                            <div class="product-image bg-light d-flex align-items-center justify-content-center me-3">
                                <i class="fas fa-image fa-2x text-muted"></i>
                            </div>
                        @endif
                        <div>
                            <h4 class="mb-1">{{ $product->name }}</h4>
                            <p class="text-muted mb-0">
                                <span class="badge bg-{{ $product->status == 'published' ? 'success' : 'secondary' }}">{{ ucfirst($product->status) }}</span>
                                @if($product->selling_price)
                                    <span class="ms-2">₹{{ number_format($product->selling_price, 2) }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Edit Product
                        </a>
                        <a href="{{ route('frontend.product.show', $product->slug) }}" class="btn btn-outline-success" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i> View Product
                        </a>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.analytics.products.show', $product) }}" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label small fw-semibold">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label small fw-semibold">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Apply Filter
                                </button>
                                <a href="{{ route('admin.analytics.products.show', $product) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Overview Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary text-white rounded-circle me-3">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-secondary mb-1 small">Total Views</h6>
                                        <h3 class="mb-0 fw-bold">{{ number_format($totalViews) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success text-white rounded-circle me-3">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-secondary mb-1 small">Unique Visitors</h6>
                                        <h3 class="mb-0 fw-bold">{{ number_format($uniqueVisitors) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-info text-white rounded-circle me-3">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-secondary mb-1 small">Previous Period</h6>
                                        <h3 class="mb-0 fw-bold">{{ number_format($previousViews) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-{{ $viewsChange >= 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ $viewsChange >= 0 ? 'success' : 'danger' }} rounded-circle me-3">
                                        <i class="fas fa-{{ $viewsChange >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-secondary mb-1 small">Change</h6>
                                        <h3 class="mb-0 fw-bold text-{{ $viewsChange >= 0 ? 'success' : 'danger' }}">
                                            {{ $viewsChange >= 0 ? '+' : '' }}{{ $viewsChange }}%
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-3 mb-4">
                    <!-- Daily Views Chart -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-area text-primary me-2"></i>Views Trend
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="dailyViewsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Device Distribution -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-mobile-alt text-primary me-2"></i>Device Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-sm">
                                    <canvas id="deviceChart"></canvas>
                                </div>
                                <div class="mt-3">
                                    @foreach($viewsByDevice as $device)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-capitalize">
                                            @if($device->device_type == 'desktop')
                                                <i class="fas fa-desktop me-2 text-primary"></i>
                                            @elseif($device->device_type == 'mobile')
                                                <i class="fas fa-mobile-alt me-2 text-success"></i>
                                            @elseif($device->device_type == 'tablet')
                                                <i class="fas fa-tablet-alt me-2 text-info"></i>
                                            @else
                                                <i class="fas fa-question me-2 text-secondary"></i>
                                            @endif
                                            {{ $device->device_type ?? 'Unknown' }}
                                        </span>
                                        <span class="badge bg-secondary">{{ number_format($device->count) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Row Charts -->
                <div class="row g-3 mb-4">
                    <!-- Hourly Distribution -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clock text-primary me-2"></i>Hourly Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="hourlyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Browser Distribution -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-globe text-primary me-2"></i>Browser Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-sm">
                                    <canvas id="browserChart"></canvas>
                                </div>
                                <div class="mt-3">
                                    @foreach($viewsByBrowser as $browser)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>
                                            @if($browser->browser == 'Chrome')
                                                <i class="fab fa-chrome me-2 text-warning"></i>
                                            @elseif($browser->browser == 'Firefox')
                                                <i class="fab fa-firefox me-2 text-danger"></i>
                                            @elseif($browser->browser == 'Safari')
                                                <i class="fab fa-safari me-2 text-info"></i>
                                            @elseif($browser->browser == 'Edge')
                                                <i class="fab fa-edge me-2 text-primary"></i>
                                            @else
                                                <i class="fas fa-globe me-2 text-secondary"></i>
                                            @endif
                                            {{ $browser->browser ?? 'Unknown' }}
                                        </span>
                                        <span class="badge bg-secondary">{{ number_format($browser->count) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referrer Sources -->
                @if($referrerSources->count() > 0)
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-link text-primary me-2"></i>Traffic Sources (Referrers)
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">Referrer URL</th>
                                                <th class="border-0 text-center">Views</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($referrerSources as $source)
                                            <tr>
                                                <td>
                                                    <a href="{{ $source->referrer }}" target="_blank" class="text-decoration-none text-truncate d-block" style="max-width: 500px;">
                                                        {{ $source->referrer }}
                                                        <i class="fas fa-external-link-alt ms-1 small"></i>
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary">{{ number_format($source->count) }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Location Analytics for this Product -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-globe text-primary me-2"></i>Views by Country
                                </h5>
                                <span class="badge bg-primary">Top 10</span>
                            </div>
                            <div class="card-body p-0">
                                @if(isset($productCountryViews) && $productCountryViews->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">Country</th>
                                                <th class="border-0 text-center">Views</th>
                                                <th class="border-0 text-end">Share</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $totalCountryViews = $productCountryViews->sum('view_count'); @endphp
                                            @foreach($productCountryViews as $country)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($country->country_code)
                                                            <img src="https://flagcdn.com/24x18/{{ strtolower($country->country_code) }}.png" 
                                                                 alt="{{ $country->country }}" 
                                                                 class="me-2" 
                                                                 style="width: 24px; height: 18px; object-fit: cover; border-radius: 2px;"
                                                                 onerror="this.style.display='none'">
                                                        @else
                                                            <i class="fas fa-flag text-muted me-2"></i>
                                                        @endif
                                                        <span class="fw-medium">{{ $country->country ?? 'Unknown' }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary">{{ number_format($country->view_count) }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-muted">{{ $totalCountryViews > 0 ? number_format(($country->view_count / $totalCountryViews) * 100, 1) : 0 }}%</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-globe fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0">No location data available yet</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-city text-info me-2"></i>Views by City
                                </h5>
                                <span class="badge bg-info">Top 10</span>
                            </div>
                            <div class="card-body p-0">
                                @if(isset($productCityViews) && $productCityViews->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">City</th>
                                                <th class="border-0 text-center">Country</th>
                                                <th class="border-0 text-end">Views</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($productCityViews as $city)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                                        <span class="fw-medium">{{ $city->city ?? 'Unknown' }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if($city->country_code)
                                                        <img src="https://flagcdn.com/16x12/{{ strtolower($city->country_code) }}.png" 
                                                             alt="{{ $city->country }}" 
                                                             class="me-1" 
                                                             style="width: 16px; height: 12px; object-fit: cover; border-radius: 2px;"
                                                             onerror="this.style.display='none'">
                                                    @endif
                                                    <small class="text-muted">{{ $city->country ?? 'Unknown' }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <span class="badge bg-info">{{ number_format($city->view_count) }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-city fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0">No city data available yet</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Views -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history text-primary me-2"></i>Recent Views (Last 50)
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="recentViewsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">Visitor</th>
                                                <th class="border-0">Device</th>
                                                <th class="border-0">Browser</th>
                                                <th class="border-0">Location</th>
                                                <th class="border-0">IP Address</th>
                                                <th class="border-0">Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentViews as $view)
                                            <tr>
                                                <td>
                                                    @if($view->user)
                                                        <span class="badge bg-primary">{{ $view->user->name }}</span>
                                                        <br><small class="text-muted">{{ $view->user->email }}</small>
                                                    @else
                                                        <span class="badge bg-secondary">Guest</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-capitalize">
                                                        @if($view->device_type == 'desktop')
                                                            <i class="fas fa-desktop text-primary"></i>
                                                        @elseif($view->device_type == 'mobile')
                                                            <i class="fas fa-mobile-alt text-success"></i>
                                                        @elseif($view->device_type == 'tablet')
                                                            <i class="fas fa-tablet-alt text-info"></i>
                                                        @endif
                                                        {{ $view->device_type ?? 'Unknown' }}
                                                    </span>
                                                </td>
                                                <td>{{ $view->browser ?? 'Unknown' }}</td>
                                                <td>
                                                    @if($view->city || $view->country)
                                                        <div class="d-flex align-items-center">
                                                            @if($view->country_code)
                                                                <img src="https://flagcdn.com/16x12/{{ strtolower($view->country_code) }}.png" 
                                                                     alt="{{ $view->country }}" 
                                                                     class="me-1" 
                                                                     style="width: 16px; height: 12px; object-fit: cover; border-radius: 2px;"
                                                                     onerror="this.style.display='none'">
                                                            @endif
                                                            <small>{{ $view->city ?? '' }}{{ $view->city && $view->country ? ', ' : '' }}{{ $view->country ?? '' }}</small>
                                                        </div>
                                                    @else
                                                        <small class="text-muted">Unknown</small>
                                                    @endif
                                                </td>
                                                <td><code>{{ $view->ip_address ?? 'N/A' }}</code></td>
                                                <td>
                                                    <span title="{{ $view->created_at->format('Y-m-d H:i:s') }}">
                                                        {{ $view->created_at->diffForHumans() }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    No views recorded for this product
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
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables for Recent Views
    if ($('#recentViewsTable').length && $('#recentViewsTable tbody tr').length > 0) {
        $('#recentViewsTable').DataTable({
            "order": [[5, "desc"]], // Sort by Time (descending)
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "language": {
                "search": "Search views:",
                "lengthMenu": "Show _MENU_ views per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ views",
                "infoEmpty": "No views available",
                "infoFiltered": "(filtered from _MAX_ total views)",
                "zeroRecords": "No matching views found",
                "emptyTable": "No views recorded for this product"
            },
            "responsive": true,
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
        });
    }

    // Daily Views Chart
    const dailyViewsCtx = document.getElementById('dailyViewsChart').getContext('2d');
    new Chart(dailyViewsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyViews->pluck('date')) !!},
            datasets: [{
                label: 'Total Views',
                data: {!! json_encode($dailyViews->pluck('views')) !!},
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Unique Visitors',
                data: {!! json_encode($dailyViews->pluck('unique_visitors')) !!},
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Device Distribution Chart
    const deviceCtx = document.getElementById('deviceChart').getContext('2d');
    new Chart(deviceCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($viewsByDevice->pluck('device_type')->map(fn($d) => ucfirst($d ?? 'Unknown'))) !!},
            datasets: [{
                data: {!! json_encode($viewsByDevice->pluck('count')) !!},
                backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Hourly Distribution Chart
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($hourlyData, 'hour')) !!},
            datasets: [{
                label: 'Views',
                data: {!! json_encode(array_column($hourlyData, 'views')) !!},
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                borderRadius: 4
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
                    beginAtZero: true
                }
            }
        }
    });

    // Browser Distribution Chart
    const browserCtx = document.getElementById('browserChart').getContext('2d');
    new Chart(browserCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($viewsByBrowser->pluck('browser')) !!},
            datasets: [{
                data: {!! json_encode($viewsByBrowser->pluck('count')) !!},
                backgroundColor: ['#ffc107', '#dc3545', '#0dcaf0', '#0d6efd', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endsection
