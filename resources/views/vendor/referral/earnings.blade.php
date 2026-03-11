@extends('vendor.layouts.app')

@section('title', 'My Referral Earnings')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Referral Earnings'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0 fw-bold">Referral Earnings</h4>
                        <p class="text-muted mb-0">Track your referral commissions</p>
                    </div>
                    <a href="{{ route('vendor.referral.my-code') }}" class="btn rounded-pill" style="background-color: var(--theme-color); border-color: var(--theme-color); color: white;">
                        <i class="fas fa-gift me-2"></i>My Referral Code
                    </a>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-6 col-lg-3 mb-3">
                        <div class="card border-0 shadow-sm bg-warning text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white-50">Pending</h6>
                                        <h3 class="mb-0 fw-bold text-white">₹{{ number_format($stats['total_pending'], 2) }}</h3>
                                        <small class="text-white-50">{{ $stats['count_pending'] }} earnings</small>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-clock fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 mb-3">
                        <div class="card border-0 shadow-sm bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white-50">Approved</h6>
                                        <h3 class="mb-0 fw-bold text-white">₹{{ number_format($stats['total_approved'], 2) }}</h3>
                                        <small class="text-white-50">{{ $stats['count_approved'] }} earnings</small>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-check-circle fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 mb-3">
                        <div class="card border-0 shadow-sm bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white-50">Paid</h6>
                                        <h3 class="mb-0 fw-bold text-white">₹{{ number_format($stats['total_paid'], 2) }}</h3>
                                        <small class="text-white-50">{{ $stats['count_paid'] }} earnings</small>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-money-bill-wave fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 mb-3">
                        <div class="card border-0 shadow-sm bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white-50">Total</h6>
                                        <h3 class="mb-0 fw-bold text-white">₹{{ number_format($stats['total_all_time'], 2) }}</h3>
                                        <small class="text-white-50">All time</small>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-chart-line fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle fa-lg me-3 mt-1"></i>
                        <div>
                            <h6 class="mb-1 fw-bold">How it works</h6>
                            <p class="mb-0">You earn 10% commission when vendors use your referral code to purchase a subscription. Earnings need admin approval before payout can be processed.</p>
                        </div>
                    </div>
                </div>

                <!-- Earnings Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-list me-2"></i>Earnings History
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4">Date</th>
                                        <th>Referred Vendor</th>
                                        <th>Plan</th>
                                        <th>Subscription</th>
                                        <th>Commission</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($earnings as $earning)
                                    <tr>
                                        <td class="px-4">
                                            <div>
                                                <div class="fw-semibold">{{ $earning->created_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $earning->created_at->diffForHumans() }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-semibold">{{ $earning->referredVendor->store_name }}</div>
                                                <small class="text-muted">{{ $earning->referredVendor->business_email }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill">{{ $earning->subscription->plan->name }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">₹{{ number_format($earning->subscription_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">₹{{ number_format($earning->commission_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            @if($earning->status == 'pending')
                                                <span class="badge bg-warning rounded-pill">
                                                    <i class="fas fa-clock me-1"></i>Pending
                                                </span>
                                            @elseif($earning->status == 'approved')
                                                <div>
                                                    <span class="badge bg-success rounded-pill">
                                                        <i class="fas fa-check-circle me-1"></i>Approved
                                                    </span>
                                                    <br><small class="text-muted">Awaiting payout</small>
                                                </div>
                                            @elseif($earning->status == 'paid')
                                                <div>
                                                    <span class="badge bg-primary rounded-pill">
                                                        <i class="fas fa-money-bill-wave me-1"></i>Paid
                                                    </span>
                                                    @if($earning->paid_at)
                                                    <br><small class="text-muted">{{ $earning->paid_at->format('M d, Y') }}</small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="badge bg-secondary rounded-pill">{{ ucfirst($earning->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="py-4">
                                                <i class="fas fa-inbox fa-4x text-muted mb-3 d-block"></i>
                                                <h5 class="text-muted mb-2">No Referral Earnings Yet</h5>
                                                <p class="text-muted mb-3">Start earning by sharing your referral code!</p>
                                                <a href="{{ route('vendor.referral.my-code') }}" class="btn rounded-pill" style="background-color: var(--theme-color); border-color: var(--theme-color); color: white;">
                                                    <i class="fas fa-gift me-2"></i>View My Referral Code
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($earnings->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $earnings->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
}

/* Dynamic theme color button styles */
.btn[style*="--theme-color"] {
    transition: all 0.3s ease;
}

.btn[style*="--theme-color"]:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn[style*="--theme-color"]:active {
    transform: translateY(0);
}
</style>
@endpush
