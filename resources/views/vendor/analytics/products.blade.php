@extends('vendor.layouts.app')

@section('title', 'Product Analytics')

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
    .chart-container-sm {
        position: relative;
        height: 200px;
    }
    .product-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
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
                <!-- Date Range Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('vendor.analytics.products') }}" class="row g-3 align-items-end">
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
                                <a href="{{ route('vendor.analytics.products') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Overview Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary text-white p-2 rounded-circle me-3">
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
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success text-white p-2 rounded-circle me-3">
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
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-info text-white p-2 rounded-circle me-3">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-secondary mb-1 small">Products Viewed</h6>
                                        <h3 class="mb-0 fw-bold">{{ number_format($productsViewed) }}</h3>
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
                                <div class="chart-container-sm">
                                    <canvas id="deviceChart"></canvas>
                                </div>
                                <div class="mt-3">
                                    @foreach($viewsByDevice as $device)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted">
                                                @if($device->device_type == 'mobile')
                                                    <i class="fas fa-mobile-alt me-2"></i>
                                                @elseif($device->device_type == 'tablet')
                                                    <i class="fas fa-tablet-alt me-2"></i>
                                                @else
                                                    <i class="fas fa-desktop me-2"></i>
                                                @endif
                                                {{ ucfirst($device->device_type ?? 'Unknown') }}
                                            </span>
                                            <span class="fw-bold">{{ number_format($device->count) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Most Viewed Products -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-fire text-danger me-2"></i>Most Viewed Products
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0 px-4 py-3">Product</th>
                                                <th class="border-0 py-3 text-end pe-4">Views</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($mostViewedProducts as $item)
                                                <tr>
                                                    <td class="px-4">
                                                        <div class="d-flex align-items-center">
                                                            @if($item->product && $item->product->mainPhoto)
                                                                <img src="{{ $item->product->mainPhoto->url }}" 
                                                                     class="product-thumb me-3" alt="">
                                                            @else
                                                                <div class="product-thumb bg-light d-flex align-items-center justify-content-center me-3">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-0 fw-medium">{{ $item->product->name ?? 'Unknown' }}</h6>
                                                                <small class="text-muted">₹{{ number_format($item->product->selling_price ?? 0, 2) }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <span class="badge bg-primary badge bg-success text-white rounded-pill px-3">
                                                            {{ number_format($item->view_count) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-center py-4 text-muted">
                                                        No product views in this period
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Least Viewed Products -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-eye-slash text-warning me-2"></i>Least Viewed Products
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0 px-4 py-3">Product</th>
                                                <th class="border-0 py-3 text-end pe-4">Views</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($leastViewedProducts as $item)
                                                <tr>
                                                    <td class="px-4">
                                                        <div class="d-flex align-items-center">
                                                            @if($item->product && $item->product->mainPhoto)
                                                                <img src="{{ $item->product->mainPhoto->url }}" 
                                                                     class="product-thumb me-3" alt="">
                                                            @else
                                                                <div class="product-thumb bg-light d-flex align-items-center justify-content-center me-3">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-0 fw-medium">{{ $item->product->name ?? 'Unknown' }}</h6>
                                                                <small class="text-muted">₹{{ number_format($item->product->selling_price ?? 0, 2) }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <span class="badge bg-warning badge bg-success text-white rounded-pill px-3">
                                                            {{ number_format($item->view_count) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-center py-4 text-muted">
                                                        No product views in this period
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

                <!-- Products with No Views -->
                @if($productsWithNoViews->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>Products with No Views
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3">Product</th>
                                        <th class="border-0 py-3">Price</th>
                                        <th class="border-0 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productsWithNoViews as $product)
                                        <tr>
                                            <td class="px-4">
                                                <div class="d-flex align-items-center">
                                                    @if($product->mainPhoto)
                                                        <img src="{{ asset('storage/' . $product->mainPhoto->file_path) }}" 
                                                             class="product-thumb me-3" alt="">
                                                    @else
                                                        <div class="product-thumb bg-light d-flex align-items-center justify-content-center me-3">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <h6 class="mb-0 fw-medium">{{ $product->name }}</h6>
                                                </div>
                                            </td>
                                            <td>₹{{ number_format($product->selling_price, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $product->status == 'published' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $product->status == 'published' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($product->status) }}
                                                </span>
                                            </td>
                                            {{-- Edit button removed - products module disabled for vendor panel --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Recent Views -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-clock text-info me-2"></i>Recent Views
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3">Product</th>
                                        <th class="border-0 py-3">Visitor</th>
                                        <th class="border-0 py-3">Device</th>
                                        <th class="border-0 py-3">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentViews as $view)
                                        <tr>
                                            <td class="px-4">
                                                @if($view->product)
                                                    <a href="{{ route('vendor.analytics.products.show', $view->product) }}" class="text-decoration-none">
                                                        {{ $view->product->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Unknown Product</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($view->user)
                                                    <span class="badge bg-success badge bg-success text-white">{{ $view->user->name }}</span>
                                                @else
                                                    <span class="text-muted">Guest</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($view->device_type == 'mobile')
                                                    <i class="fas fa-mobile-alt text-muted"></i>
                                                @elseif($view->device_type == 'tablet')
                                                    <i class="fas fa-tablet-alt text-muted"></i>
                                                @else
                                                    <i class="fas fa-desktop text-muted"></i>
                                                @endif
                                            </td>
                                            <td class="text-muted">{{ $view->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
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
                    display: false
                }
            }
        }
    });
</script>
@endpush