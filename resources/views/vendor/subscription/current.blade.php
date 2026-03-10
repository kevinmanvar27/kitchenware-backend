@extends('vendor.layouts.app')

@section('title', 'Current Subscription')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Current Subscription'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0 fw-bold">Current Subscription</h4>
                        <p class="text-muted mb-0">Manage your subscription plan</p>
                    </div>
                    <a href="{{ route('vendor.subscription.plans') }}" class="btn btn-primary rounded-pill">
                        <i class="fas fa-th-large me-2"></i>View All Plans
                    </a>
                </div>

                <div class="row">
                    <!-- Subscription Details -->
                    <div class="col-lg-8 mb-4">
                        <!-- Main Subscription Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fas fa-crown me-2 text-warning"></i>Subscription Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small mb-1">Plan Name</label>
                                        <h4 class="mb-0 fw-bold">{{ $subscription->plan->name }}</h4>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small mb-1">Status</label>
                                        <div>
                                            <span class="badge {{ $subscription->status_badge_class }} fs-6 rounded-pill px-3 py-2">
                                                {{ ucfirst($subscription->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small mb-1">Amount Paid</label>
                                        <h5 class="mb-0 fw-bold text-success">₹{{ number_format($subscription->amount_paid, 2) }}</h5>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small mb-1">Billing Cycle</label>
                                        <p class="mb-0 fw-semibold">{{ ucfirst($subscription->plan->billing_cycle) }}</p>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small mb-1">Started On</label>
                                        <p class="mb-0">
                                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                                            <span class="fw-semibold">{{ $subscription->starts_at ? $subscription->starts_at->format('M d, Y') : 'N/A' }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small mb-1">
                                            @if($subscription->ends_at)
                                                Expires On
                                            @else
                                                Validity
                                            @endif
                                        </label>
                                        <p class="mb-0">
                                            <i class="fas fa-calendar-check text-success me-2"></i>
                                            @if($subscription->ends_at)
                                                <span class="fw-semibold">{{ $subscription->ends_at->format('M d, Y') }}</span>
                                                @if($subscription->isActive())
                                                    <span class="badge bg-info ms-2 rounded-pill">
                                                        {{ $subscription->daysRemaining() }} days left
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-success fw-semibold">Lifetime</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if($subscription->trial_ends_at)
                                    <div class="alert alert-info border-0 mb-0 mt-3">
                                        <i class="fas fa-gift me-2"></i>
                                        <strong>Trial Period:</strong> 
                                        @if($subscription->onTrial())
                                            Active until {{ $subscription->trial_ends_at->format('M d, Y') }}
                                        @else
                                            Ended on {{ $subscription->trial_ends_at->format('M d, Y') }}
                                        @endif
                                    </div>
                                @endif

                                @if($subscription->payment_id && $subscription->payment_id !== 'free_plan')
                                    <div class="mt-3">
                                        <label class="text-muted small mb-1">Payment ID</label>
                                        <p class="mb-0 font-monospace small bg-light p-2 rounded">{{ $subscription->payment_id }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Plan Features Card -->
                        @if($subscription->plan->features)
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="mb-0 fw-semibold">
                                        <i class="fas fa-list-check me-2"></i>Plan Features
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($subscription->plan->features ?? [] as $feature)
                                            <div class="col-md-6 mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <span>{{ $feature }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4 mb-4">
                        <!-- Quick Actions Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fas fa-bolt me-2"></i>Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('vendor.subscription.plans') }}" class="btn btn-primary rounded-pill">
                                        <i class="fas fa-arrow-up me-2"></i>Upgrade Plan
                                    </a>
                                    
                                    <a href="{{ route('vendor.subscription.history') }}" class="btn btn-outline-secondary rounded-pill">
                                        <i class="fas fa-history me-2"></i>View History
                                    </a>
                                    
                                    @if($subscription->isActive() && $subscription->status !== 'cancelled')
                                        <button class="btn btn-outline-danger rounded-pill" onclick="cancelSubscription()">
                                            <i class="fas fa-times-circle me-2"></i>Cancel Subscription
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Subscription Info Card -->
                        <div class="card border-0 shadow-sm border-primary">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Need Help?
                                </h6>
                                <p class="card-text small mb-3">
                                    If you have any questions about your subscription or need to make changes, please contact our support team.
                                </p>
                                <a href="mailto:support@example.com" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fas fa-envelope me-1"></i>Contact Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Cancel Subscription
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to cancel your subscription?</p>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> You will lose access to all vendor panel features immediately after cancellation.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>No, Keep It
                </button>
                <button type="button" class="btn btn-danger rounded-pill" onclick="confirmCancel()">
                    <i class="fas fa-check me-1"></i>Yes, Cancel
                </button>
            </div>
        </div>
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

.font-monospace {
    font-family: 'Courier New', monospace;
}
</style>
@endpush

@push('scripts')
<script>
    let cancelModal;

    document.addEventListener('DOMContentLoaded', function() {
        cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
    });

    function cancelSubscription() {
        cancelModal.show();
    }

    function confirmCancel() {
        fetch('{{ route("vendor.subscription.cancel") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            cancelModal.hide();
            
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => {
                    window.location.href = '{{ route("vendor.subscription.plans") }}';
                }, 2000);
            } else {
                showAlert('error', data.message || 'Failed to cancel subscription');
            }
        })
        .catch(error => {
            cancelModal.hide();
            console.error('Error:', error);
            showAlert('error', 'Failed to cancel subscription. Please try again.');
        });
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          'alert-info';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        const container = document.querySelector('.main-content .pt-4');
        if (container) {
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    }
</script>
@endpush
