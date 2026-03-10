@extends('admin.layouts.app')

@section('title', 'Vendor Earnings & Commission Report')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Vendor Earnings & Commission Report'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-2 text-gray-800">
                            <i class="fas fa-chart-line me-2"></i>Vendor Earnings & Commission Report
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.vendor-payments.index') }}">Vendor Payments</a></li>
                                <li class="breadcrumb-item active">Earnings Report</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="{{ route('admin.vendor-payments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Payments
                        </a>
                        <button type="button" class="btn btn-success" onclick="exportReport()">
                            <i class="fas fa-file-export me-1"></i> Export CSV
                        </button>
                    </div>
                </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-primary border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Total Vendors
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $stats['total_vendors'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-store fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Period Commission Earned
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                ₹{{ number_format($stats['period_total_commission'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-info border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Lifetime Total Earned
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                ₹{{ number_format($stats['lifetime_total_earned'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-warning border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Pending Payouts
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                ₹{{ number_format($stats['lifetime_pending'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-filter me-2"></i>Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.vendor-payments.earnings-report') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ $startDate }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ $endDate }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="search" class="form-label">Search Vendor</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Store name, email...">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('admin.vendor-payments.earnings-report') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Period Summary -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Period Summary ({{ $startDate }} to {{ $endDate }})</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Total Order Amount:</strong></td>
                            <td class="text-end">₹{{ number_format($stats['period_total_orders'], 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Commission Earned:</strong></td>
                            <td class="text-end text-success fw-bold">₹{{ number_format($stats['period_total_commission'], 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Vendor Earnings:</strong></td>
                            <td class="text-end">₹{{ number_format($stats['period_total_vendor_earnings'], 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Lifetime Summary</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Total Earned (All Vendors):</strong></td>
                            <td class="text-end">₹{{ number_format($stats['lifetime_total_earned'], 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Paid Out:</strong></td>
                            <td class="text-end text-info">₹{{ number_format($stats['lifetime_total_paid'], 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Pending:</strong></td>
                            <td class="text-end text-warning fw-bold">₹{{ number_format($stats['lifetime_pending'], 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Breakdown -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-percentage me-2"></i>Commission Rate Distribution
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Commission Rate</th>
                            <th>Number of Vendors</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissionBreakdown as $breakdown)
                        <tr>
                            <td>{{ $breakdown->commission_rate ?? 0 }}%</td>
                            <td>{{ $breakdown->vendor_count }}</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ ($breakdown->vendor_count / $stats['total_vendors']) * 100 }}%">
                                        {{ number_format(($breakdown->vendor_count / $stats['total_vendors']) * 100, 1) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">No data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Performing Vendors -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-trophy me-2"></i>Top 5 Vendors by Commission (Period)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Vendor</th>
                            <th>Commission Rate</th>
                            <th>Orders</th>
                            <th>Order Amount</th>
                            <th>Commission</th>
                            <th>Vendor Earning</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topVendors as $index => $vendorData)
                        <tr>
                            <td>
                                @if($index === 0)
                                    <i class="fas fa-trophy text-warning"></i> #1
                                @elseif($index === 1)
                                    <i class="fas fa-medal text-secondary"></i> #2
                                @elseif($index === 2)
                                    <i class="fas fa-medal text-danger"></i> #3
                                @else
                                    #{{ $index + 1 }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.vendor-payments.show', $vendorData['vendor']->id) }}">
                                    {{ $vendorData['vendor']->store_name }}
                                </a>
                            </td>
                            <td>{{ $vendorData['commission_rate'] }}%</td>
                            <td>{{ $vendorData['total_orders'] }}</td>
                            <td>₹{{ number_format($vendorData['period_order_amount'], 2) }}</td>
                            <td class="text-success fw-bold">₹{{ number_format($vendorData['period_commission'], 2) }}</td>
                            <td>₹{{ number_format($vendorData['period_vendor_earning'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No vendors found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- All Vendors Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-table me-2"></i>All Vendors - Detailed Report
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="vendorsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Vendor</th>
                            <th>Owner</th>
                            <th>Commission Rate</th>
                            <th>Period Orders</th>
                            <th>Period Order Amount</th>
                            <th>Period Commission</th>
                            <th>Period Vendor Earning</th>
                            <th>Lifetime Earned</th>
                            <th>Lifetime Paid</th>
                            <th>Pending</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendorData)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $vendorData['vendor']->store_logo_url }}" 
                                         alt="{{ $vendorData['vendor']->store_name }}" 
                                         class="rounded-circle me-2" 
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                    <div>
                                        <strong>{{ $vendorData['vendor']->store_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $vendorData['vendor']->business_email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $vendorData['vendor']->user->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $vendorData['commission_rate'] }}%</span>
                            </td>
                            <td>{{ $vendorData['total_orders'] }}</td>
                            <td>₹{{ number_format($vendorData['period_order_amount'], 2) }}</td>
                            <td class="text-success fw-bold">₹{{ number_format($vendorData['period_commission'], 2) }}</td>
                            <td>₹{{ number_format($vendorData['period_vendor_earning'], 2) }}</td>
                            <td>₹{{ number_format($vendorData['lifetime_earned'], 2) }}</td>
                            <td class="text-info">₹{{ number_format($vendorData['lifetime_paid'], 2) }}</td>
                            <td class="text-warning">₹{{ number_format($vendorData['pending_amount'], 2) }}</td>
                            <td>
                                <a href="{{ route('admin.vendor-payments.show', $vendorData['vendor']->id) }}" 
                                   class="btn btn-sm btn-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.vendors.show', $vendorData['vendor']->id) }}" 
                                   class="btn btn-sm btn-info" title="View Vendor">
                                    <i class="fas fa-store"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center">No vendors found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">TOTALS:</th>
                            <th>{{ $vendors->sum('total_orders') }}</th>
                            <th>₹{{ number_format($vendors->sum('period_order_amount'), 2) }}</th>
                            <th class="text-success">₹{{ number_format($vendors->sum('period_commission'), 2) }}</th>
                            <th>₹{{ number_format($vendors->sum('period_vendor_earning'), 2) }}</th>
                            <th>₹{{ number_format($vendors->sum('lifetime_earned'), 2) }}</th>
                            <th class="text-info">₹{{ number_format($vendors->sum('lifetime_paid'), 2) }}</th>
                            <th class="text-warning">₹{{ number_format($vendors->sum('pending_amount'), 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#vendorsTable').DataTable({
            "order": [[ 5, "desc" ]], // Sort by period commission
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "dom": 'Blfrtip',
            "buttons": []
        });
    });

    // Export report function
    function exportReport() {
        const form = document.getElementById('filterForm');
        const url = new URL('{{ route("admin.vendor-payments.earnings-report.export") }}', window.location.origin);
        
        // Add form parameters to URL
        const formData = new FormData(form);
        for (let [key, value] of formData.entries()) {
            url.searchParams.append(key, value);
        }
        
        window.location.href = url.toString();
    }
</script>
@endpush
@endsection
