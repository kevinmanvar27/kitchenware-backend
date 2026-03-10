@extends('vendor.layouts.app')

@section('title', 'Profile Settings')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Profile Settings'])
            
            @section('page-title', 'Profile Settings')
            
            <div class="pt-4 pb-2 mb-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="row">
                    <!-- Profile Information -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-user me-2 text-primary"></i>Profile Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="text-center mb-4">
                                        <div class="position-relative d-inline-block">
                                            @if($vendor && $vendor->store_logo_url)
                                                <img src="{{ $vendor->store_logo_url }}" alt="{{ $vendor->store_name }}" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                                    <span class="text-white display-4">{{ strtoupper(substr($vendor->store_name ?? 'V', 0, 1)) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label fw-bold">Your Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control rounded-pill px-4" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control rounded-pill px-4" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mobile_number" class="form-label fw-bold">Mobile Number</label>
                                        <input type="text" class="form-control rounded-pill px-4" id="mobile_number" name="mobile_number" value="{{ old('mobile_number', $user->mobile_number) }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="store_name" class="form-label fw-bold">Store Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-pill px-4" id="store_name" name="store_name" value="{{ old('store_name', $vendor->store_name ?? '') }}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="store_description" class="form-label fw-bold">Store Description</label>
                                        <textarea class="form-control" id="store_description" name="store_description" rows="3">{{ old('store_description', $vendor->store_description ?? '') }}</textarea>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="business_email" class="form-label fw-bold">Business Email</label>
                                            <input type="email" class="form-control rounded-pill px-4" id="business_email" name="business_email" value="{{ old('business_email', $vendor->business_email ?? '') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="business_phone" class="form-label fw-bold">Business Phone</label>
                                            <input type="text" class="form-control rounded-pill px-4" id="business_phone" name="business_phone" value="{{ old('business_phone', $vendor->business_phone ?? '') }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="gst_number" class="form-label fw-bold">GST Number</label>
                                            <input type="text" class="form-control rounded-pill px-4" id="gst_number" name="gst_number" value="{{ old('gst_number', $vendor->gst_number ?? '') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="pan_number" class="form-label fw-bold">PAN Number</label>
                                            <input type="text" class="form-control rounded-pill px-4" id="pan_number" name="pan_number" value="{{ old('pan_number', $vendor->pan_number ?? '') }}">
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-theme rounded-pill">
                                            <i class="fas fa-save me-2"></i>Update Profile
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Business Address -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Business Address
                                </h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('vendor.profile.update-address') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="mb-3">
                                        <label for="address" class="form-label fw-bold">Street Address</label>
                                        <input type="text" class="form-control rounded-pill px-4" id="address" name="address" value="{{ old('address', $vendor->business_address ?? '') }}">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="city" class="form-label fw-bold">City</label>
                                            <input type="text" class="form-control rounded-pill px-4" id="city" name="city" value="{{ old('city', $vendor->city ?? '') }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="state" class="form-label fw-bold">State/Province</label>
                                            <input type="text" class="form-control rounded-pill px-4" id="state" name="state" value="{{ old('state', $vendor->state ?? '') }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="postal_code" class="form-label fw-bold">Postal Code</label>
                                            <input type="text" class="form-control rounded-pill px-4" id="postal_code" name="postal_code" value="{{ old('postal_code', $vendor->postal_code ?? '') }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="country" class="form-label fw-bold">Country</label>
                                            <input type="text" class="form-control rounded-pill px-4" id="country" name="country" value="{{ old('country', $vendor->country ?? '') }}">
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-theme rounded-pill">
                                            <i class="fas fa-save me-2"></i>Update Address
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Change Password -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-lock me-2 text-primary"></i>Change Password
                                </h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('vendor.profile.update-password') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label fw-bold">Current Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control rounded-pill px-4" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-bold">New Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control rounded-pill px-4" id="password" name="password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label fw-bold">Confirm New Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control rounded-pill px-4" id="password_confirmation" name="password_confirmation" required>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-warning rounded-pill">
                                            <i class="fas fa-key me-2"></i>Change Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Status -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>Account Status
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Account Status</span>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'suspended' => 'danger',
                                                'rejected' => 'dark'
                                            ];
                                            $vendorStatus = $vendor->status ?? 'pending';
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$vendorStatus] ?? 'secondary' }} rounded-pill px-3 py-2">
                                            {{ ucfirst($vendorStatus) }}
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Member Since</span>
                                        <span class="fw-bold">{{ $vendor->created_at ? $vendor->created_at->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Commission Rate</span>
                                        <span class="fw-bold">{{ $vendor->commission_rate ?? 0 }}%</span>
                                    </div>
                                    
                                    @if($vendor && $vendor->approved_at)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Approved On</span>
                                        <span class="fw-bold">{{ $vendor->approved_at->format('M d, Y') }}</span>
                                    </div>
                                    @endif
                                </div>
                                
                                @if($vendorStatus === 'pending')
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        Your account is pending approval. You will be notified once it's approved.
                                    </div>
                                @elseif($vendorStatus === 'suspended')
                                    <div class="alert alert-danger mb-0">
                                        <i class="fas fa-ban me-2"></i>
                                        Your account has been suspended. Please contact support for more information.
                                    </div>
                                @elseif($vendorStatus === 'rejected')
                                    <div class="alert alert-danger mb-0">
                                        <i class="fas fa-times-circle me-2"></i>
                                        Your account has been rejected.
                                        @if($vendor && $vendor->rejection_reason)
                                            <br><strong>Reason:</strong> {{ $vendor->rejection_reason }}
                                        @endif
                                    </div>
                                @elseif($vendorStatus === 'approved')
                                    <div class="alert alert-success mb-0">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Your account is active and in good standing.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bank Details -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-university me-2 text-primary"></i>Bank Account Details
                                </h5>
                                <a href="{{ route('vendor.profile.bank-details') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i>Manage Bank Details
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-muted">Account Status</span>
                                            @if($vendor->primaryBankAccount && $vendor->primaryBankAccount->is_verified)
                                                <span class="badge bg-success rounded-pill px-3 py-2">Verified</span>
                                            @else
                                                <span class="badge bg-warning rounded-pill px-3 py-2">Pending Verification</span>
                                            @endif
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-muted">Account Holder</span>
                                            <span class="fw-bold">{{ $vendor->primaryBankAccount->account_holder_name ?? 'Not Set' }}</span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-muted">Account Number</span>
                                            <span class="fw-bold">{{ $vendor->primaryBankAccount ? '••••••' . substr($vendor->primaryBankAccount->account_number, -4) : 'Not Set' }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-muted">Bank Name</span>
                                            <span class="fw-bold">{{ $vendor->primaryBankAccount->bank_name ?? 'Not Set' }}</span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-muted">IFSC Code</span>
                                            <span class="fw-bold">{{ $vendor->primaryBankAccount->ifsc_code ?? 'Not Set' }}</span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-muted">Account Type</span>
                                            <span class="fw-bold">{{ $vendor->primaryBankAccount->account_type ? ucfirst($vendor->primaryBankAccount->account_type) : 'Not Set' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Store Logo & Banner -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-image me-2 text-primary"></i>Store Logo
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    @if($vendor && $vendor->store_logo_url)
                                        <img src="{{ $vendor->store_logo_url }}" alt="Store Logo" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 200px; height: 200px; margin: 0 auto;">
                                            <i class="fas fa-store fa-4x text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <form action="{{ route('vendor.profile.store-logo.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <input type="file" class="form-control" name="store_logo" accept="image/*" required>
                                        <div class="form-text">Recommended size: 200x200px. Max 2MB.</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-theme flex-grow-1">
                                            <i class="fas fa-upload me-2"></i>Upload Logo
                                        </button>
                                        @if($vendor && $vendor->store_logo)
                                            <a href="{{ route('vendor.profile.store-logo.remove') }}" class="btn btn-outline-danger" onclick="event.preventDefault(); document.getElementById('remove-logo-form').submit();">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        @endif
                                    </div>
                                </form>
                                @if($vendor && $vendor->store_logo)
                                    <form id="remove-logo-form" action="{{ route('vendor.profile.store-logo.remove') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-panorama me-2 text-primary"></i>Store Banner
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    @if($vendor && $vendor->store_banner_url)
                                        <img src="{{ $vendor->store_banner_url }}" alt="Store Banner" class="img-thumbnail" style="max-width: 100%; max-height: 200px;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 150px;">
                                            <i class="fas fa-panorama fa-4x text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <form action="{{ route('vendor.profile.store-banner.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <input type="file" class="form-control" name="store_banner" accept="image/*" required>
                                        <div class="form-text">Recommended size: 1200x300px. Max 4MB.</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-theme flex-grow-1">
                                            <i class="fas fa-upload me-2"></i>Upload Banner
                                        </button>
                                        @if($vendor && $vendor->store_banner)
                                            <a href="{{ route('vendor.profile.store-banner.remove') }}" class="btn btn-outline-danger" onclick="event.preventDefault(); document.getElementById('remove-banner-form').submit();">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        @endif
                                    </div>
                                </form>
                                @if($vendor && $vendor->store_banner)
                                    <form id="remove-banner-form" action="{{ route('vendor.profile.store-banner.remove') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection
