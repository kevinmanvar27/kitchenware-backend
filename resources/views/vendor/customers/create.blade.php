@extends('vendor.layouts.app')

@section('title', 'Add Customer')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Add Customer'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Back Button -->
                <div class="mb-4">
                    <a href="{{ route('vendor.customers.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i>Back to Customers
                    </a>
                </div>

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold">Create New Customer</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('vendor.customers.store') }}" method="POST">
                                    @csrf
                                    
                                    <!-- Login Credentials Section -->
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="fas fa-key me-2"></i>Login Credentials
                                    </h6>
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" name="email" value="{{ old('email') }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Customer will use this email to login</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                   id="password" name="password" required minlength="6">
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Minimum 6 characters</div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Personal Information Section -->
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="fas fa-user me-2"></i>Personal Information
                                    </h6>
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="mobile_number" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control @error('mobile_number') is-invalid @enderror" 
                                                   id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}">
                                            @error('mobile_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Address Section -->
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="fas fa-map-marker-alt me-2"></i>Address Information
                                    </h6>
                                    <div class="row mb-4">
                                        <div class="col-12 mb-3">
                                            <label for="address" class="form-label">Street Address</label>
                                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                                      id="address" name="address" rows="2">{{ old('address') }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                                   id="city" name="city" value="{{ old('city') }}">
                                            @error('city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="state" class="form-label">State</label>
                                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                                   id="state" name="state" value="{{ old('state') }}">
                                            @error('state')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="postal_code" class="form-label">Postal Code</label>
                                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                                   id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                                            @error('postal_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Business Settings Section -->
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="fas fa-cog me-2"></i>Business Settings
                                    </h6>
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3">
                                            <label for="discount_percentage" class="form-label">Default Discount (%)</label>
                                            <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" 
                                                   id="discount_percentage" name="discount_percentage" 
                                                   value="{{ old('discount_percentage', 0) }}" min="0" max="100" step="0.01">
                                            @error('discount_percentage')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">This discount will be automatically applied to customer's orders</div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('vendor.customers.index') }}" class="btn btn-outline-secondary rounded-pill me-2">
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary rounded-pill">
                                            <i class="fas fa-save me-2"></i>Create Customer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Help Card -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm bg-light">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-info-circle text-primary me-2"></i>Information
                                </h6>
                                <p class="small text-muted mb-3">
                                    Creating a customer account allows them to:
                                </p>
                                <ul class="small text-muted ps-3">
                                    <li class="mb-2">Login to your store using their email and password</li>
                                    <li class="mb-2">Browse and purchase products from your catalog only</li>
                                    <li class="mb-2">View their order history and track deliveries</li>
                                    <li class="mb-2">Receive automatic discounts if configured</li>
                                </ul>
                                <hr>
                                <p class="small text-muted mb-0">
                                    <strong>Note:</strong> Customers can only see products from your store. They cannot access other vendors' products.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection