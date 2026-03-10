@extends('admin.layouts.app')

@section('title', 'Vendor Details')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Vendor Details'])
            
            <div class="pt-4 pb-2 mb-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="row">
                    <!-- Vendor Profile Card -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                @if($vendor->store_logo_url)
                                    <img src="{{ $vendor->store_logo_url }}" alt="{{ $vendor->store_name }}" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 120px; height: 120px;">
                                        <span class="text-white display-4">{{ strtoupper(substr($vendor->store_name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                
                                <h4 class="fw-bold mb-1">{{ $vendor->store_name }}</h4>
                                <p class="text-muted mb-3">{{ $vendor->store_slug }}</p>
                                
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'suspended' => 'danger',
                                        'rejected' => 'dark'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$vendor->status] ?? 'secondary' }} rounded-pill px-4 py-2 mb-3">
                                    {{ ucfirst($vendor->status) }}
                                </span>
                                
                                <div class="d-grid gap-2 mt-3">
                                    <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-theme rounded-pill">
                                        <i class="fas fa-edit me-2"></i>Edit Vendor
                                    </a>
                                    
                                    @if($vendor->status === 'pending')
                                        <form action="{{ route('admin.vendors.approve', $vendor) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success rounded-pill w-100">
                                                <i class="fas fa-check me-2"></i>Approve Vendor
                                            </button>
                                        </form>
                                    @elseif($vendor->status === 'approved')
                                        <form action="{{ route('admin.vendors.suspend', $vendor) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-warning rounded-pill w-100">
                                                <i class="fas fa-ban me-2"></i>Suspend Vendor
                                            </button>
                                        </form>
                                    @elseif($vendor->status === 'suspended')
                                        <form action="{{ route('admin.vendors.reactivate', $vendor) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success rounded-pill w-100">
                                                <i class="fas fa-redo me-2"></i>Reactivate Vendor
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Stats -->
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Quick Stats</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Products</span>
                                    <span class="fw-bold">{{ $vendor->products_count ?? 0 }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Orders</span>
                                    <span class="fw-bold">{{ $vendor->orders_count ?? 0 }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Total Sales</span>
                                    <span class="fw-bold">${{ number_format($vendor->total_sales ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Commission Rate</span>
                                    <span class="fw-bold">{{ $vendor->commission_rate ?? 0 }}%</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Member Since</span>
                                    <span class="fw-bold">{{ $vendor->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vendor Details -->
                    <div class="col-lg-8">
                        <!-- Contact Information -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-address-card me-2 text-primary"></i>Contact Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Business Email</label>
                                        <p class="fw-medium mb-0">{{ $vendor->business_email }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Phone Number</label>
                                        <p class="fw-medium mb-0">{{ $vendor->business_phone ?? 'Not provided' }}</p>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="text-muted small">Address</label>
                                        <p class="fw-medium mb-0">
                                            @if($vendor->business_address)
                                                {{ $vendor->business_address }}<br>
                                                {{ $vendor->city }}, {{ $vendor->state }} {{ $vendor->postal_code }}<br>
                                                {{ $vendor->country }}
                                            @else
                                                Not provided
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-12">
                                        <label class="text-muted small">Description</label>
                                        <p class="fw-medium mb-0">{{ $vendor->store_description ?? 'No description provided' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Owner -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-user me-2 text-primary"></i>Account Owner
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($vendor->user)
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $vendor->user->avatar_url ?? asset('images/default-avatar.png') }}" class="rounded-circle me-3" width="60" height="60" alt="{{ $vendor->user->name }}">
                                        <div>
                                            <h5 class="fw-bold mb-1">{{ $vendor->user->name }}</h5>
                                            <p class="text-muted mb-0">{{ $vendor->user->email }}</p>
                                            <small class="text-muted">Joined {{ $vendor->user->created_at->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No user account linked</p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Recent Products -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-box me-2 text-primary"></i>Recent Products
                                </h5>
                                @if(isset($vendor->products) && $vendor->products->count() > 0)
                                    <a href="#" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
                                @endif
                            </div>
                            <div class="card-body">
                                @if(isset($vendor->products) && $vendor->products->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Stock</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($vendor->products->take(5) as $product)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                @if($product->image)
                                                                    <img src="{{ asset('storage/' . $product->image) }}" class="rounded me-2" width="40" height="40" alt="{{ $product->name }}">
                                                                @else
                                                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                        <i class="fas fa-image text-muted"></i>
                                                                    </div>
                                                                @endif
                                                                <span class="fw-medium">{{ Str::limit($product->name, 30) }}</span>
                                                            </div>
                                                        </td>
                                                        <td>${{ number_format($product->price, 2) }}</td>
                                                        <td>{{ $product->stock ?? 0 }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}-subtle text-{{ $product->is_active ? 'success' : 'secondary' }}-emphasis rounded-pill">
                                                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No products yet</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Back Button -->
                <div class="mt-4">
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i>Back to Vendors
                    </a>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>
@endsection
