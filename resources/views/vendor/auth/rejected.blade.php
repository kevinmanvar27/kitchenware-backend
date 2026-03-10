@extends('vendor.layouts.app')

@section('title', 'Account Rejected')

@section('content')
<div class="container-fluid">
    <div class="row min-vh-100 align-items-center justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fas fa-times-circle fa-3x text-danger"></i>
                        </div>
                    </div>
                    
                    <h2 class="fw-bold mb-3">Account Rejected</h2>
                    
                    <p class="text-muted mb-4">
                        We're sorry, but your vendor application has been rejected. 
                        Please review the reason below and contact support if you have any questions.
                    </p>
                    
                    @if($vendor && $vendor->rejection_reason)
                    <div class="alert alert-danger text-start mb-4">
                        <h6 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Rejection Reason</h6>
                        <p class="mb-0">{{ $vendor->rejection_reason }}</p>
                    </div>
                    @endif
                    
                    @if($vendor)
                    <div class="bg-light rounded p-4 mb-4 text-start">
                        <h6 class="fw-bold mb-3">Application Details</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Store Name</small>
                                <p class="mb-2 fw-bold">{{ $vendor->store_name }}</p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Submitted On</small>
                                <p class="mb-2 fw-bold">{{ $vendor->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        If you believe this was a mistake, please contact our support team at 
                        <a href="mailto:{{ setting('support_email', 'support@example.com') }}">{{ setting('support_email', 'support@example.com') }}</a>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('vendor.register') }}" class="btn btn-theme rounded-pill">
                            <i class="fas fa-redo me-2"></i>Apply Again
                        </a>
                        <a href="{{ route('vendor.login') }}" class="btn btn-outline-secondary rounded-pill">
                            <i class="fas fa-arrow-left me-2"></i>Back to Login
                        </a>
                        <form action="{{ route('vendor.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger rounded-pill w-100">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection