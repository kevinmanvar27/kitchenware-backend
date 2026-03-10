@extends('vendor.layouts.app')

@section('title', 'Account Pending Approval')

@section('content')
<div class="container-fluid">
    <div class="row min-vh-100 align-items-center justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fas fa-clock fa-3x text-warning"></i>
                        </div>
                    </div>
                    
                    <h2 class="fw-bold mb-3">Account Pending Approval</h2>
                    
                    <p class="text-muted mb-4">
                        Thank you for registering as a vendor! Your account is currently under review. 
                        Our team will review your application and get back to you within 24-48 hours.
                    </p>
                    
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
                        You will receive an email notification once your account is approved.
                    </div>
                    
                    <div class="d-grid gap-2">
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