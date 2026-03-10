@extends('admin.layouts.app')

@section('title', 'Edit Vendor')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Edit Vendor'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Edit Vendor</h4>
                                    <p class="mb-0 text-muted">{{ $vendor->store_name }}</p>
                                </div>
                                <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </a>
                            </div>
                            
                            <div class="card-body">
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
                                
                                <form action="{{ route('admin.vendors.update', $vendor) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <!-- Store Information Section -->
                                        <div class="col-md-6">
                                            <h5 class="fw-bold mb-3 border-bottom pb-2">
                                                <i class="fas fa-store me-2 text-primary"></i>Store Information
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label for="store_name" class="form-label fw-bold">Store Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4" id="store_name" name="store_name" value="{{ old('store_name', $vendor->store_name) }}" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="store_slug" class="form-label fw-bold">Store Slug</label>
                                                <input type="text" class="form-control rounded-pill px-4" id="store_slug" name="store_slug" value="{{ old('store_slug', $vendor->store_slug) }}">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="business_email" class="form-label fw-bold">Business Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control rounded-pill px-4" id="business_email" name="business_email" value="{{ old('business_email', $vendor->business_email) }}" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="business_phone" class="form-label fw-bold">Phone Number</label>
                                                <input type="text" class="form-control rounded-pill px-4" id="business_phone" name="business_phone" value="{{ old('business_phone', $vendor->business_phone) }}">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="store_description" class="form-label fw-bold">Store Description</label>
                                                <textarea class="form-control" id="store_description" name="store_description" rows="3">{{ old('store_description', $vendor->store_description) }}</textarea>
                                            </div>
                                        </div>
                                        
                                        <!-- Settings Section -->
                                        <div class="col-md-6">
                                            <h5 class="fw-bold mb-3 border-bottom pb-2">
                                                <i class="fas fa-cog me-2 text-primary"></i>Settings
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                                <select class="form-select rounded-pill" id="status" name="status" required>
                                                    <option value="pending" {{ old('status', $vendor->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="approved" {{ old('status', $vendor->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                                    <option value="suspended" {{ old('status', $vendor->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                    <option value="rejected" {{ old('status', $vendor->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="commission_rate" class="form-label fw-bold">Commission Rate (%)</label>
                                                <input type="number" class="form-control rounded-pill px-4" id="commission_rate" name="commission_rate" value="{{ old('commission_rate', $vendor->commission_rate) }}" min="0" max="100" step="0.01">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="store_logo" class="form-label fw-bold">Store Logo</label>
                                                @if($vendor->store_logo_url)
                                                    <div class="mb-2">
                                                        <img src="{{ $vendor->store_logo_url }}" alt="{{ $vendor->store_name }}" class="img-thumbnail" style="max-height: 100px;">
                                                    </div>
                                                @endif
                                                <input type="file" class="form-control" id="store_logo" name="store_logo" accept="image/*">
                                                <div class="form-text">Leave empty to keep current logo</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Account Owner</label>
                                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                                    <img src="{{ $vendor->user->avatar_url ?? asset('images/default-avatar.png') }}" class="rounded-circle me-3" width="40" height="40" alt="{{ $vendor->user->name ?? 'N/A' }}">
                                                    <div>
                                                        <div class="fw-medium">{{ $vendor->user->name ?? 'N/A' }}</div>
                                                        <small class="text-muted">{{ $vendor->user->email ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
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
                                                <label for="business_address" class="form-label fw-bold">Street Address</label>
                                                <input type="text" class="form-control rounded-pill px-4" id="business_address" name="business_address" value="{{ old('business_address', $vendor->business_address) }}">
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="city" class="form-label fw-bold">City</label>
                                                    <input type="text" class="form-control rounded-pill px-4" id="city" name="city" value="{{ old('city', $vendor->city) }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="state" class="form-label fw-bold">State/Province</label>
                                                    <input type="text" class="form-control rounded-pill px-4" id="state" name="state" value="{{ old('state', $vendor->state) }}">
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="postal_code" class="form-label fw-bold">Postal Code</label>
                                                    <input type="text" class="form-control rounded-pill px-4" id="postal_code" name="postal_code" value="{{ old('postal_code', $vendor->postal_code) }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="country" class="form-label fw-bold">Country</label>
                                                    <input type="text" class="form-control rounded-pill px-4" id="country" name="country" value="{{ old('country', $vendor->country) }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Statistics -->
                                        <div class="col-md-6">
                                            <h5 class="fw-bold mb-3 border-bottom pb-2">
                                                <i class="fas fa-chart-bar me-2 text-primary"></i>Statistics
                                            </h5>
                                            
                                            <div class="row g-3">
                                                <div class="col-6">
                                                    <div class="p-3 bg-primary-subtle rounded text-center">
                                                        <div class="h4 mb-0 fw-bold text-primary">{{ $vendor->products_count ?? 0 }}</div>
                                                        <small class="text-muted">Products</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-3 bg-success-subtle rounded text-center">
                                                        <div class="h4 mb-0 fw-bold text-success">{{ $vendor->orders_count ?? 0 }}</div>
                                                        <small class="text-muted">Orders</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-3 bg-info-subtle rounded text-center">
                                                        <div class="h4 mb-0 fw-bold text-info">${{ number_format($vendor->total_sales ?? 0, 2) }}</div>
                                                        <small class="text-muted">Total Sales</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-3 bg-warning-subtle rounded text-center">
                                                        <div class="h4 mb-0 fw-bold text-warning">{{ $vendor->created_at->diffForHumans() }}</div>
                                                        <small class="text-muted">Member Since</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Update Vendor
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
