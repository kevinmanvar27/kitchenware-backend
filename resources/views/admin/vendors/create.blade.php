@extends('admin.layouts.app')

@section('title', 'Add New Vendor')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Add New Vendor'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Add New Vendor</h4>
                                    <p class="mb-0 text-muted">Create a new vendor account</p>
                                </div>
                                <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </a>
                            </div>
                            
                            <div class="card-body">
                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                <form action="{{ route('admin.vendors.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="row">
                                        <!-- User Account Section -->
                                        <div class="col-md-6">
                                            <h5 class="fw-bold mb-3 border-bottom pb-2">
                                                <i class="fas fa-user me-2 text-primary"></i>User Account
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="email" class="form-label fw-bold">Login Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control rounded-pill px-4 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="mobile_number" class="form-label fw-bold">Mobile Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4 @error('mobile_number') is-invalid @enderror" id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}" required>
                                                @error('mobile_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="password" class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control rounded-pill px-4 @error('password') is-invalid @enderror" id="password" name="password" required>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="password_confirmation" class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control rounded-pill px-4" id="password_confirmation" name="password_confirmation" required>
                                            </div>
                                        </div>
                                        
                                        <!-- Store Information Section -->
                                        <div class="col-md-6">
                                            <h5 class="fw-bold mb-3 border-bottom pb-2">
                                                <i class="fas fa-store me-2 text-primary"></i>Store Information
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label for="store_name" class="form-label fw-bold">Store Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4 @error('store_name') is-invalid @enderror" id="store_name" name="store_name" value="{{ old('store_name') }}" required>
                                                @error('store_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="store_description" class="form-label fw-bold">Store Description</label>
                                                <textarea class="form-control @error('store_description') is-invalid @enderror" id="store_description" name="store_description" rows="3">{{ old('store_description') }}</textarea>
                                                @error('store_description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="business_email" class="form-label fw-bold">Business Email</label>
                                                <input type="email" class="form-control rounded-pill px-4 @error('business_email') is-invalid @enderror" id="business_email" name="business_email" value="{{ old('business_email') }}" placeholder="Uses login email if empty">
                                                @error('business_email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="business_phone" class="form-label fw-bold">Business Phone</label>
                                                <input type="text" class="form-control rounded-pill px-4 @error('business_phone') is-invalid @enderror" id="business_phone" name="business_phone" value="{{ old('business_phone') }}" placeholder="Uses mobile number if empty">
                                                @error('business_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="row">
                                        <!-- Address Section -->
                                        <div class="col-md-6">
                                            <h5 class="fw-bold mb-3 border-bottom pb-2">
                                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Address
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label for="business_address" class="form-label fw-bold">Business Address <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4 @error('business_address') is-invalid @enderror" id="business_address" name="business_address" value="{{ old('business_address') }}" required>
                                                @error('business_address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="city" class="form-label fw-bold">City <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control rounded-pill px-4 @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" required>
                                                    @error('city')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="state" class="form-label fw-bold">State/Province <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control rounded-pill px-4 @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state') }}" required>
                                                    @error('state')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="postal_code" class="form-label fw-bold">Postal Code <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control rounded-pill px-4 @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required>
                                                    @error('postal_code')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="country" class="form-label fw-bold">Country <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control rounded-pill px-4 @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country') }}" required>
                                                    @error('country')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Settings Section -->
                                        <div class="col-md-6">
                                            <h5 class="fw-bold mb-3 border-bottom pb-2">
                                                <i class="fas fa-cog me-2 text-primary"></i>Settings
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                                <select class="form-select rounded-pill @error('status') is-invalid @enderror" id="status" name="status" required>
                                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="commission_rate" class="form-label fw-bold">Commission Rate (%)</label>
                                                <input type="number" class="form-control rounded-pill px-4 @error('commission_rate') is-invalid @enderror" id="commission_rate" name="commission_rate" value="{{ old('commission_rate', 10) }}" min="0" max="100" step="0.01">
                                                @error('commission_rate')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="gst_number" class="form-label fw-bold">GST Number</label>
                                                <input type="text" class="form-control rounded-pill px-4 @error('gst_number') is-invalid @enderror" id="gst_number" name="gst_number" value="{{ old('gst_number') }}">
                                                @error('gst_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="pan_number" class="form-label fw-bold">PAN Number</label>
                                                <input type="text" class="form-control rounded-pill px-4 @error('pan_number') is-invalid @enderror" id="pan_number" name="pan_number" value="{{ old('pan_number') }}">
                                                @error('pan_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Create Vendor
                                        </button>
                                    </div>
                                </form>
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
