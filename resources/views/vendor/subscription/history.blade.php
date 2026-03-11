@extends('vendor.layouts.app')

@section('title', 'Subscription History')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Subscription History'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0 fw-bold">Subscription History</h4>
                        <p class="text-muted mb-0">View all your past and current subscriptions</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('vendor.subscription.current') }}" class="btn btn-theme-outline rounded-pill">
                            <i class="fas fa-crown me-1"></i><span class="d-none d-sm-inline"> Current</span>
                        </a>
                        <a href="{{ route('vendor.subscription.plans') }}" class="btn btn-theme-primary rounded-pill">
                            <i class="fas fa-th-large me-1"></i><span class="d-none d-sm-inline"> View Plans</span>
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                @if($subscriptions->count() > 0)
                <div class="row mb-4">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm text-white h-100" style="background-color: var(--theme-color);">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white-50">Total</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $subscriptions->total() }}</h3>
                                        <small class="text-white-50">Subscriptions</small>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-history fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white-50">Active</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $subscriptions->where('status', 'active')->count() }}</h3>
                                        <small class="text-white-50">Plans</small>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-check-circle fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-warning text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white-50">Expired</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $subscriptions->where('status', 'expired')->count() }}</h3>
                                        <small class="text-white-50">Plans</small>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-clock fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white-50">Total Spent</h6>
                                        <h3 class="mb-0 fw-bold text-white">₹{{ number_format($subscriptions->sum('amount_paid'), 0) }}</h3>
                                        <small class="text-white-50">All time</small>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-rupee-sign fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Subscriptions Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-list me-2"></i>All Subscriptions
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($subscriptions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-4">Plan</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Started</th>
                                            <th>Expires</th>
                                            <th>Duration</th>
                                            <th>Payment ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subscriptions as $sub)
                                            <tr>
                                                <td class="px-4">
                                                    <div>
                                                        <div class="fw-semibold">{{ $sub->plan->name }}</div>
                                                        <small class="text-muted">{{ ucfirst($sub->plan->billing_cycle) }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success">₹{{ number_format($sub->amount_paid, 2) }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $sub->status_badge_class }} rounded-pill">
                                                        {{ ucfirst($sub->status) }}
                                                    </span>
                                                    @if($sub->isActive() && $sub->ends_at)
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $sub->daysRemaining() }} days left
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($sub->starts_at)
                                                        <div>
                                                            <div class="fw-semibold">{{ $sub->starts_at->format('M d, Y') }}</div>
                                                            <small class="text-muted">{{ $sub->starts_at->format('h:i A') }}</small>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($sub->ends_at)
                                                        <div>
                                                            <div class="fw-semibold">{{ $sub->ends_at->format('M d, Y') }}</div>
                                                            <small class="text-muted">{{ $sub->ends_at->format('h:i A') }}</small>
                                                        </div>
                                                    @else
                                                        <span class="badge bg-success rounded-pill">Lifetime</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($sub->starts_at && $sub->ends_at)
                                                        <span class="fw-semibold">{{ $sub->starts_at->diffInDays($sub->ends_at) }}</span> days
                                                    @else
                                                        <span class="text-muted">∞</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($sub->payment_id && $sub->payment_id !== 'free_plan')
                                                        <code class="small bg-light px-2 py-1 rounded">{{ Str::limit($sub->payment_id, 15) }}</code>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($subscriptions->hasPages())
                            <div class="card-footer bg-white border-0 py-3">
                                {{ $subscriptions->links() }}
                            </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-history fa-4x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted mb-2">No Subscription History</h5>
                                    <p class="text-muted mb-4">You haven't subscribed to any plan yet.</p>
                                    <a href="{{ route('vendor.subscription.plans') }}" class="btn btn-theme-primary rounded-pill px-4">
                                        <i class="fas fa-shopping-cart me-2"></i>Browse Plans
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
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

code {
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    color: #d63384;
    font-weight: 500;
}

/* Theme-based button styles */
.btn-theme-primary {
    background-color: var(--theme-color);
    border-color: var(--theme-color);
    color: white;
}

.btn-theme-primary:hover {
    background-color: var(--theme-color);
    border-color: var(--theme-color);
    color: white;
    opacity: 0.9;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-theme-outline {
    color: var(--theme-color);
    border-color: var(--theme-color);
    background-color: transparent;
}

.btn-theme-outline:hover {
    background-color: var(--theme-color);
    border-color: var(--theme-color);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.text-theme {
    color: var(--theme-color) !important;
}

.border-theme {
    border-color: var(--theme-color) !important;
}
</style>
@endpush
