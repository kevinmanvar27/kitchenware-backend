@extends('vendor.layouts.app')

@section('title', 'Edit Customer - ' . $customer->name)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Edit Customer'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Back Button -->
                <div class="mb-4">
                    <a href="{{ route('vendor.customers.show', $customer->id) }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i>Back to Customer
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
                                <h5 class="mb-0 fw-bold">Edit Customer: {{ $customer->name }}</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('vendor.customers.update', $customer->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <!-- Login Info (Read-only) -->
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="fas fa-key me-2"></i>Login Information
                                    </h6>
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email Address</label>
                                            <input type="email" class="form-control" value="{{ $customer->email }}" disabled readonly>
                                            <div class="form-text">Email cannot be changed. Use "Reset Password" to update login credentials.</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Account Status</label>
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                                       {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    Active (can login and place orders)
                                                </label>
                                            </div>
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
                                                   id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="mobile_number" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control @error('mobile_number') is-invalid @enderror" 
                                                   id="mobile_number" name="mobile_number" value="{{ old('mobile_number', $customer->mobile_number) }}">
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
                                                      id="address" name="address" rows="2">{{ old('address', $customer->address) }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                                   id="city" name="city" value="{{ old('city', $customer->city) }}">
                                            @error('city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="state" class="form-label">State</label>
                                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                                   id="state" name="state" value="{{ old('state', $customer->state) }}">
                                            @error('state')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="postal_code" class="form-label">Postal Code</label>
                                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                                   id="postal_code" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}">
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
                                                   value="{{ old('discount_percentage', $customer->discount_percentage ?? 0) }}" 
                                                   min="0" max="100" step="0.01">
                                            @error('discount_percentage')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">This discount will be automatically applied to customer's orders</div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                            <i class="fas fa-trash me-2"></i>Delete Customer
                                        </button>
                                        <div>
                                            <a href="{{ route('vendor.customers.show', $customer->id) }}" class="btn btn-outline-secondary rounded-pill me-2">
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary rounded-pill">
                                                <i class="fas fa-save me-2"></i>Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Side Panel -->
                    <div class="col-lg-4">
                        <!-- Customer Info Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body text-center py-4">
                                @if($customer->profile_avatar_url)
                                    <img src="{{ $customer->profile_avatar_url }}" alt="{{ $customer->name }}" 
                                         class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                         style="width: 80px; height: 80px; font-size: 2rem;">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                @endif
                                <h5 class="fw-bold mb-1">{{ $customer->name }}</h5>
                                <p class="text-muted mb-2">{{ $customer->email }}</p>
                                @if($customer->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </div>
                        </div>

                        <!-- Profile Avatar Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-camera text-primary me-2"></i>Profile Avatar
                                </h6>
                                <form action="{{ route('vendor.customers.upload-avatar', $customer->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                                               id="avatar" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif">
                                        @error('avatar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Accepted: JPEG, PNG, JPG, GIF. Max: 2MB</div>
                                    </div>
                                    <button type="submit" class="btn btn-outline-primary w-100 rounded-pill mb-2">
                                        <i class="fas fa-upload me-2"></i>Upload Avatar
                                    </button>
                                </form>
                                @if($customer->profile_avatar_url)
                                    <form action="{{ route('vendor.customers.remove-avatar', $customer->id) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to remove the avatar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger w-100 rounded-pill">
                                            <i class="fas fa-trash me-2"></i>Remove Avatar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Reset Password Card -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-key text-primary me-2"></i>Reset Password
                                </h6>
                                <p class="small text-muted mb-3">
                                    Set a new password for this customer. They will be logged out of all devices.
                                </p>
                                <button type="button" class="btn btn-outline-primary w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                    <i class="fas fa-key me-2"></i>Reset Password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $customer->name }}</strong>?</p>
                <p class="text-danger mb-0">This action cannot be undone. The customer will lose access to their account and order history.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('vendor.customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill">Delete Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('vendor.customers.reset-password', $customer->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Reset Customer Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <div class="form-text">Minimum 6 characters</div>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection