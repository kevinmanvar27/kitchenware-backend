@extends('admin.layouts.app')

@section('title', 'Product Analytics - ' . config('app.name', 'Laravel'))

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
    .product-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }
    .device-icon {
        font-size: 1.5rem;
        opacity: 0.7;
    }
    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .analytics-badge {
        font-size: 0.7rem;
        padding: 0.25em 0.5em;
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
                <!-- Date Range Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.analytics.products') }}" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label small fw-semibold">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label small fw-semibold">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Apply Filter
                                </button>
                                <a href="{{ route('admin.analytics.products') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </a>
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="{{ route('admin.analytics.products.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success">
                                    <i class="fas fa-download me-1"></i> Export CSV
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
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-info text-white rounded-circle me-3">
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

                <!-- User Engagement -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <h6 class="text-secondary mb-3">
                                    <i class="fas fa-user-check text-success me-2"></i>User Engagement
                                </h6>
                                <div class="row">
                                    <div class="col-6 text-center border-end">
                                        <h4 class="fw-bold text-primary mb-1">{{ number_format($loggedInViews) }}</h4>
                                        <small class="text-secondary">Logged-in Users</small>
                                    </div>
                                    <div class="col-6 text-center">
                                        <h4 class="fw-bold text-secondary mb-1">{{ number_format($guestViews) }}</h4>
                                        <small class="text-secondary">Guest Visitors</small>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 8px;">
                                    @php
                                        $loggedInPercent = $totalViews > 0 ? ($loggedInViews / $totalViews) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-primary" style="width: {{ $loggedInPercent }}%"></div>
                                    <div class="progress-bar bg-secondary" style="width: {{ 100 - $loggedInPercent }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm stat-card h-100">
                            <div class="card-body">
                                <h6 class="text-secondary mb-3">
                                    <i class="fas fa-tags text-info me-2"></i>Views by Category
                                </h6>
                                @if(count($viewsByCategory) > 0)
                                    @foreach(array_slice($viewsByCategory, 0, 5, true) as $categoryId => $catData)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-truncate" style="max-width: 200px;">{{ $catData['name'] }}</span>
                                        <span class="badge bg-info">{{ number_format($catData['views']) }} views</span>
                                    </div>
                                    @endforeach
                                @else
                                    <p class="text-muted mb-0">No category data available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Most Viewed Products -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-fire text-danger me-2"></i>Most Viewed Products
                                </h5>
                                <span class="badge bg-success">Top 10</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">Product</th>
                                                <th class="border-0 text-center">Views</th>
                                                <th class="border-0 text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($mostViewedProducts as $index => $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-{{ $index < 3 ? 'warning' : 'secondary' }} me-2">{{ $index + 1 }}</span>
                                                        @if($item->product && $item->product->mainPhoto)
                                                            <img src="{{ $item->product->mainPhoto->url }}" alt="{{ $item->product->name }}" class="product-thumb me-2">
                                                        @else
                                                            <div class="product-thumb bg-light d-flex align-items-center justify-content-center me-2">
                                                                <i class="fas fa-image text-muted"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <span class="fw-medium">{{ Str::limit($item->product->name ?? 'Unknown', 30) }}</span>
                                                            @if($item->product && $item->product->selling_price)
                                                                <br><small class="text-muted">₹{{ number_format($item->product->selling_price, 2) }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary">{{ number_format($item->view_count) }}</span>
                                                </td>
                                                <td class="text-end">
                                                    @if($item->product)
                                                    <a href="{{ route('admin.analytics.products.show', $item->product) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">
                                                    <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                                                    No product views recorded yet
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
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-snowflake text-info me-2"></i>Least Viewed Products
                                </h5>
                                <span class="badge bg-warning">Needs Attention</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">Product</th>
                                                <th class="border-0 text-center">Views</th>
                                                <th class="border-0 text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($leastViewedProducts as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($item->product && $item->product->mainPhoto)
                                                            <img src="{{ $item->product->mainPhoto->url }}" alt="{{ $item->product->name }}" class="product-thumb me-2">
                                                        @else
                                                            <div class="product-thumb bg-light d-flex align-items-center justify-content-center me-2">
                                                                <i class="fas fa-image text-muted"></i>
                                                            </div>
                                                        @endif
                                                        <span class="fw-medium">{{ Str::limit($item->product->name ?? 'Unknown', 30) }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">{{ number_format($item->view_count) }}</span>
                                                </td>
                                                <td class="text-end">
                                                    @if($item->product)
                                                    <a href="{{ route('admin.analytics.products.show', $item->product) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">
                                                    No data available
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
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-eye-slash text-danger me-2"></i>Products with No Views
                                </h5>
                                <span class="badge bg-danger">{{ $productsWithNoViews->count() }} products</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach($productsWithNoViews as $product)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="border rounded p-2 d-flex align-items-center">
                                            @if($product->mainPhoto)
                                                <img src="{{ $product->mainPhoto->url }}" alt="{{ $product->name }}" class="product-thumb me-2">
                                            @else
                                                <div class="product-thumb bg-light d-flex align-items-center justify-content-center me-2">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                            <span class="small text-truncate">{{ $product->name }}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Location Analytics -->
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
                                @if($viewsByCountry->count() > 0)
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
                                            @php $totalCountryViews = $viewsByCountry->sum('view_count'); @endphp
                                            @foreach($viewsByCountry as $country)
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
                                @if($viewsByCity->count() > 0)
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
                                            @foreach($viewsByCity as $city)
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

                <!-- Conversion Data -->
                @if(count($conversionData) > 0)
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-funnel-dollar text-success me-2"></i>View to Purchase Conversion
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="conversionTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">Product</th>
                                                <th class="border-0 text-center">Views</th>
                                                <th class="border-0 text-center">Purchases</th>
                                                <th class="border-0 text-center">Conversion Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($conversionData as $data)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($data['product']->mainPhoto)
                                                            <img src="{{ $data['product']->mainPhoto->url }}" alt="{{ $data['product']->name }}" class="product-thumb me-2">
                                                        @else
                                                            <div class="product-thumb bg-light d-flex align-items-center justify-content-center me-2">
                                                                <i class="fas fa-image text-muted"></i>
                                                            </div>
                                                        @endif
                                                        <span class="fw-medium">{{ Str::limit($data['product']->name, 40) }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">{{ number_format($data['views']) }}</td>
                                                <td class="text-center">{{ number_format($data['purchases']) }}</td>
                                                <td class="text-center">
                                                    <span class="badge {{ $data['conversion_rate'] > 5 ? 'bg-success' : ($data['conversion_rate'] > 1 ? 'bg-warning' : 'bg-secondary') }}">
                                                        {{ $data['conversion_rate'] }}%
                                                    </span>
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

                <!-- Recent Views -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history text-primary me-2"></i>Recent Product Views
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="recentViewsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">Product</th>
                                                <th class="border-0">Visitor</th>
                                                <th class="border-0">Device</th>
                                                <th class="border-0">Browser</th>
                                                <th class="border-0">Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentViews as $view)
                                            <tr>
                                                <td>
                                                    @if($view->product)
                                                        <a href="{{ route('admin.analytics.products.show', $view->product) }}" class="text-decoration-none">
                                                            {{ Str::limit($view->product->name, 30) }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">Deleted Product</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($view->user)
                                                        <span class="badge bg-primary">{{ $view->user->name }}</span>
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
                                                    <small class="text-muted">{{ $view->created_at->diffForHumans() }}</small>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
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
    // Initialize DataTables for Recent Product Views
    if ($('#recentViewsTable').length && $('#recentViewsTable tbody tr').length > 0) {
        $('#recentViewsTable').DataTable({
            "order": [[4, "desc"]], // Sort by Time (descending)
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "language": {
                "search": "Search views:",
                "lengthMenu": "Show _MENU_ views per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ views",
                "infoEmpty": "No views available",
                "infoFiltered": "(filtered from _MAX_ total views)",
                "zeroRecords": "No matching views found",
                "emptyTable": "No recent views"
            },
            "responsive": true,
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
        });
    }

    // Initialize DataTables for Conversion Data
    if ($('#conversionTable').length && $('#conversionTable tbody tr').length > 0) {
        $('#conversionTable').DataTable({
            "order": [[3, "desc"]], // Sort by Conversion Rate (descending)
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "language": {
                "search": "Search products:",
                "lengthMenu": "Show _MENU_ products per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ products",
                "infoEmpty": "No products available",
                "infoFiltered": "(filtered from _MAX_ total products)",
                "zeroRecords": "No matching products found",
                "emptyTable": "No conversion data available"
            },
            "columnDefs": [
                {
                    "targets": [1, 2, 3], // Views, Purchases, Conversion Rate columns
                    "type": "num"
                }
            ],
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
