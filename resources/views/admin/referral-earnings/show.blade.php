@extends('admin.layouts.app')

@section('title', 'Referral Earning Details')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Referral Earning Details',
                'breadcrumbs' => [
                    'Referral Earnings' => route('admin.referral-earnings.index'),
                    'Details' => null
                ]
            ])

            <!-- Back Button -->
            <div class="mb-3">
                <a href="{{ route('admin.referral-earnings.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>

            <!-- Earning Overview Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-gift me-2 text-theme"></i>Earning #{{ $earning->id }}
                    </h5>
                    <div>
                        @if($earning->status == 'pending')
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Pending Approval</span>
                        @elseif($earning->status == 'approved')
                            <span class="badge bg-success rounded-pill px-3 py-2">Approved</span>
                        @elseif($earning->status == 'paid')
                            <span class="badge bg-primary rounded-pill px-3 py-2">Paid</span>
                        @else
                            <span class="badge bg-secondary rounded-pill px-3 py-2">{{ ucfirst($earning->status) }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Commission Details -->
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-4 h-100 bg-light">
                                <h6 class="text-muted mb-3 fw-semibold">
                                    <i class="fas fa-money-bill-wave me-2"></i>Commission Details
                                </h6>
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Subscription Amount</label>
                                    <div class="fs-5 fw-medium">₹{{ number_format($earning->subscription_amount, 2) }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Commission Rate</label>
                                    <div class="fs-5 fw-medium text-info">{{ $earning->commission_percentage }}%</div>
                                </div>
                                <div class="mb-0">
                                    <label class="text-muted small mb-1">Commission Amount</label>
                                    <div class="fs-4 fw-bold text-success">₹{{ number_format($earning->commission_amount, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Timeline -->
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-4 h-100 bg-light">
                                <h6 class="text-muted mb-3 fw-semibold">
                                    <i class="fas fa-clock me-2"></i>Status & Timeline
                                </h6>
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Referral Code</label>
                                    <div>
                                        <span class="badge bg-theme rounded-pill fs-6 px-3 py-2">{{ $earning->referral_code }}</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Created Date</label>
                                    <div class="fw-medium">{{ $earning->created_at->format('M d, Y h:i A') }}</div>
                                    <small class="text-muted">{{ $earning->created_at->diffForHumans() }}</small>
                                </div>
                                @if($earning->approved_at)
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Approved Date</label>
                                    <div class="fw-medium text-success">{{ $earning->approved_at->format('M d, Y h:i A') }}</div>
                                    <small class="text-muted">{{ $earning->approved_at->diffForHumans() }}</small>
                                </div>
                                @endif
                                @if($earning->paid_at)
                                <div class="mb-0">
                                    <label class="text-muted small mb-1">Paid Date</label>
                                    <div class="fw-medium text-primary">{{ $earning->paid_at->format('M d, Y h:i A') }}</div>
                                    <small class="text-muted">{{ $earning->paid_at->diffForHumans() }}</small>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vendor Information -->
            <div class="row mb-4">
                <!-- Referrer Vendor (Who Earns) -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-success text-white py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="fas fa-user-tie me-2"></i>Referrer Vendor (Earns Commission)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Store Name</label>
                                <div class="fs-5 fw-semibold">{{ $earning->referrerVendor->store_name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Business Email</label>
                                <div class="fw-medium">
                                    <i class="fas fa-envelope me-2 text-muted"></i>{{ $earning->referrerVendor->business_email }}
                                </div>
                            </div>
                            @if($earning->referrerVendor->phone)
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Phone</label>
                                <div class="fw-medium">
                                    <i class="fas fa-phone me-2 text-muted"></i>{{ $earning->referrerVendor->phone }}
                                </div>
                            </div>
                            @endif
                            <div class="mb-0">
                                <label class="text-muted small mb-1">Vendor ID</label>
                                <div class="fw-medium text-muted">#{{ $earning->referrerVendor->id }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referred Vendor (Who Purchased) -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-info text-white py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="fas fa-user-plus me-2"></i>Referred Vendor (Made Purchase)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Store Name</label>
                                <div class="fs-5 fw-semibold">{{ $earning->referredVendor->store_name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Business Email</label>
                                <div class="fw-medium">
                                    <i class="fas fa-envelope me-2 text-muted"></i>{{ $earning->referredVendor->business_email }}
                                </div>
                            </div>
                            @if($earning->referredVendor->phone)
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Phone</label>
                                <div class="fw-medium">
                                    <i class="fas fa-phone me-2 text-muted"></i>{{ $earning->referredVendor->phone }}
                                </div>
                            </div>
                            @endif
                            <div class="mb-0">
                                <label class="text-muted small mb-1">Vendor ID</label>
                                <div class="fw-medium text-muted">#{{ $earning->referredVendor->id }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-receipt me-2 text-theme"></i>Subscription Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small mb-1">Subscription ID</label>
                            <div class="fw-medium">#{{ $earning->subscription->id }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small mb-1">Plan Name</label>
                            <div class="fw-medium">{{ $earning->subscription->plan->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small mb-1">Plan Type</label>
                            <div class="fw-medium text-capitalize">{{ $earning->subscription->plan->type ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small mb-1">Subscription Status</label>
                            <div>
                                @if($earning->subscription->status == 'active')
                                    <span class="badge bg-success rounded-pill">Active</span>
                                @elseif($earning->subscription->status == 'expired')
                                    <span class="badge bg-danger rounded-pill">Expired</span>
                                @elseif($earning->subscription->status == 'cancelled')
                                    <span class="badge bg-secondary rounded-pill">Cancelled</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill">{{ ucfirst($earning->subscription->status) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small mb-1">Start Date</label>
                            <div class="fw-medium">{{ $earning->subscription->starts_at ? $earning->subscription->starts_at->format('M d, Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small mb-1">End Date</label>
                            <div class="fw-medium">{{ $earning->subscription->ends_at ? $earning->subscription->ends_at->format('M d, Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small mb-1">Subscriber</label>
                            <div class="fw-medium">{{ $earning->subscription->user->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payout Information (if paid) -->
            @if($earning->payout)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-money-check-alt me-2 text-theme"></i>Payout Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small mb-1">Payout ID</label>
                            <div class="fw-medium">#{{ $earning->payout->id }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small mb-1">Payout Amount</label>
                            <div class="fw-bold text-success">₹{{ number_format($earning->payout->amount, 2) }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small mb-1">Payout Status</label>
                            <div>
                                <span class="badge bg-primary rounded-pill">{{ ucfirst($earning->payout->status) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small mb-1">Payout Date</label>
                            <div class="fw-medium">{{ $earning->payout->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Admin Notes -->
            @if($earning->admin_notes)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-sticky-note me-2 text-theme"></i>Admin Notes
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $earning->admin_notes }}</p>
                </div>
            </div>
            @endif

            <!-- Actions -->
            @if($earning->canBeApproved())
            <div class="card shadow-sm mb-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-semibold">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>Action Required
                            </h6>
                            <p class="mb-0 text-muted">This earning is pending approval. Review the details and approve if everything is correct.</p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="fas fa-check me-2"></i>Approve Earning
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<!-- Approve Modal -->
@if($earning->canBeApproved())
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.referral-earnings.approve', $earning->id) }}" method="POST">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="approveModalLabel">Approve Referral Earning</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h6 class="mb-2">Confirm Approval</h6>
                        <p class="text-muted mb-0">You are about to approve a commission of <strong class="text-success">₹{{ number_format($earning->commission_amount, 2) }}</strong> for <strong>{{ $earning->referrerVendor->store_name }}</strong>.</p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_notes" class="form-label fw-medium">Admin Notes (Optional)</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Add any notes or comments about this approval..."></textarea>
                        <small class="text-muted">These notes will be saved with the earning record.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="fas fa-check me-2"></i>Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .card-header.bg-success,
    .card-header.bg-info {
        border: none;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>
@endpush
