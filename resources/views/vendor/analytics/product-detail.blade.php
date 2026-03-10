@extends('vendor.layouts.app')

@section('title', 'Product Analytics - ' . $product->name)

@push('styles')
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
    .product-image {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Product Analytics'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('vendor.analytics.products') }}" class="text-decoration-none">
                                <i class="fas fa-chart-bar me-1"></i>Product Analytics
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                    </ol>
                </nav>

                <!-- Product Header -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                @if($product->mainPhoto)
                                    <img src="{{ asset('storage/' . $product->mainPhoto->file_path) }}" 
                                         class="product-image" alt="{{ $product->name }}">
                                @else
                                    <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col">
                                <h3 class="mb-1 fw-bold">{{ $product->name }}</h3>
                                <p class="text-muted mb-2">{{ $product->category->name ?? 'Uncategorized' }}</p>
                                <div class="d-flex gap-3">
                                    <span class="badge bg-{{ $product->status == 'published' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $product->status == 'published' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                    <span class="text-muted">
                                        <i class="fas fa-tag me-1"></i>₹{{ number_format($product->selling_price, 2) }}
                                    </span>
                                    <span class="text-muted">
                                        <i class="fas fa-boxes me-1"></i>{{ $product->stock }} in stock
                                    </span>
                                </div>
                            </div>
                            {{-- Edit Product button removed - products module disabled for vendor panel --}}
                        </div>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('vendor.analytics.products.show', $product) }}" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label small fw-semibold">Start Date</label>
                                <input type="date" class="form-control rounded-pill" id="start_date" name="start_date" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label small fw-semibold">End Date</label>
                                <input type="date" class="form-control rounded-pill" id="end_date" name="end_date" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-theme rounded-pill px-4">
                                    <i class="fas fa-filter me-1"></i> Apply Filter
                                </button>
                                <a href="{{ route('vendor.analytics.products.show', $product) }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-secondary mb-1 small">Total Views</h6>
                                        <h3 class="mb-0 fw-bold">{{ number_format($totalViews) }}</h3>
                                        @if(isset($viewsChange))
                                            <small class="text-{{ $viewsChange >= 0 ? 'success' : 'danger' }}">
                                                <i class="fas fa-{{ $viewsChange >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                                {{ abs($viewsChange) }}%
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle me-3">
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
                                    <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle me-3">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-secondary mb-1 small">Previous Period</h6>
                                        <h3 class="mb-0 fw-bold">{{ number_format($previousViews ?? 0) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle me-3">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-secondary mb-1 small">Browsers</h6>
                                        <h3 class="mb-0 fw-bold">{{ $viewsByBrowser->count() }}</h3>
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
                                <h5 class="card-title mb-0 fw-bold">
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
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-mobile-alt text-success me-2"></i>Device Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="deviceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hourly Distribution -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-clock text-info me-2"></i>Hourly Distribution
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="hourlyChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Views -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-history text-primary me-2"></i>Recent Views
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3">Visitor</th>
                                        <th class="border-0 py-3">Device</th>
                                        <th class="border-0 py-3">Browser</th>
                                        <th class="border-0 py-3">Location</th>
                                        <th class="border-0 py-3">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentViews as $view)
                                        <tr>
                                            <td class="px-4">
                                                @if($view->user)
                                                    <span class="badge bg-success bg-opacity-10 text-success">
                                                        <i class="fas fa-user me-1"></i>{{ $view->user->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">
                                                        <i class="fas fa-user-secret me-1"></i>Guest
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($view->device_type == 'mobile')
                                                    <i class="fas fa-mobile-alt text-primary me-1"></i>Mobile
                                                @elseif($view->device_type == 'tablet')
                                                    <i class="fas fa-tablet-alt text-info me-1"></i>Tablet
                                                @else
                                                    <i class="fas fa-desktop text-secondary me-1"></i>Desktop
                                                @endif
                                            </td>
                                            <td>{{ $view->browser ?? 'Unknown' }}</td>
                                            <td>{{ $view->city ?? 'Unknown' }}, {{ $view->country ?? '' }}</td>
                                            <td class="text-muted">{{ $view->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                No recent views
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Daily Views Chart
    const dailyViewsCtx = document.getElementById('dailyViewsChart').getContext('2d');
    new Chart(dailyViewsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyViews->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
            datasets: [{
                label: 'Views',
                data: {!! json_encode($dailyViews->pluck('views')) !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2
            }, {
                label: 'Unique Visitors',
                data: {!! json_encode($dailyViews->pluck('unique_visitors')) !!},
                borderColor: '#22c55e',
                backgroundColor: 'transparent',
                tension: 0.4,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
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
                backgroundColor: [
                    '#6366f1',
                    '#22c55e',
                    '#f59e0b',
                    '#ef4444'
                ],
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

    // Hourly Distribution Chart
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($hourlyData)->pluck('hour')) !!},
            datasets: [{
                label: 'Views',
                data: {!! json_encode(collect($hourlyData)->pluck('views')) !!},
                backgroundColor: 'rgba(99, 102, 241, 0.7)',
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
</script>
@endpush