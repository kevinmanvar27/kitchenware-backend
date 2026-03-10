@extends('vendor.layouts.app')

@section('title', 'Customer Details - ' . $customer->name)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Customer Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Back Button -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="{{ route('vendor.customers.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i>Back to Customers
                    </a>
                    <div>
                        <a href="{{ route('vendor.customers.edit', $customer->id) }}" class="btn btn-outline-primary rounded-pill me-2">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                        <form action="{{ route('vendor.customers.destroy', $customer->id) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this customer?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger rounded-pill">
                                <i class="fas fa-trash me-2"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <!-- Customer Info Card -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-4">
                                @if($customer->profile_avatar_url)
                                    <img src="{{ $customer->profile_avatar_url }}" alt="{{ $customer->name }}" 
                                         class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                @else
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                         style="width: 100px; height: 100px; font-size: 2.5rem;">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                @endif
                                <h4 class="fw-bold mb-1">{{ $customer->name }}</h4>
                                <p class="text-muted mb-2">{{ $customer->email }}</p>
                                
                                @if($customer->is_active)
                                    <span class="badge bg-success mb-3">Active</span>
                                @else
                                    <span class="badge bg-danger mb-3">Inactive</span>
                                @endif
                                
                                <hr>
                                
                                <div class="text-start">
                                    <div class="mb-3">
                                        <label class="text-muted small">Phone</label>
                                        <div class="fw-medium">{{ $customer->mobile_number ?? 'Not provided' }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Address</label>
                                        <div class="fw-medium">
                                            @if($customer->address)
                                                {{ $customer->address }}<br>
                                                @if($customer->city || $customer->state || $customer->postal_code)
                                                    {{ $customer->city }}{{ $customer->city && $customer->state ? ', ' : '' }}{{ $customer->state }} {{ $customer->postal_code }}
                                                @endif
                                            @else
                                                Not provided
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Discount</label>
                                        <div class="fw-medium">{{ $customer->discount_percentage ?? 0 }}%</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Customer Since</label>
                                        <div class="fw-medium">{{ $customerSince->format('M d, Y') }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Last Login</label>
                                        <div class="fw-medium">
                                            @if($customer->last_login_at)
                                                {{ $customer->last_login_at->format('M d, Y h:i A') }}
                                            @else
                                                Never logged in
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Statistics Card -->
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Statistics</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Orders</span>
                                    <span class="fw-bold">{{ $totalOrders }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Spent</span>
                                    <span class="fw-bold text-success">₹{{ number_format($totalSpent, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Card -->
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Quick Actions</h6>
                                
                                <!-- Toggle Status -->
                                <form action="{{ route('vendor.customers.toggle-status', $customer->id) }}" method="POST" class="mb-3">
                                    @csrf
                                    <button type="submit" class="btn btn-{{ $customer->is_active ? 'warning' : 'success' }} w-100 rounded-pill">
                                        <i class="fas fa-{{ $customer->is_active ? 'ban' : 'check' }} me-2"></i>
                                        {{ $customer->is_active ? 'Deactivate Customer' : 'Activate Customer' }}
                                    </button>
                                </form>
                                
                                <!-- Reset Password -->
                                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                    <i class="fas fa-key me-2"></i>Reset Password
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Orders Section -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold">Order History</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-4 py-3">Order #</th>
                                                <th class="py-3">Date</th>
                                                <th class="py-3">Items</th>
                                                <th class="py-3">Total</th>
                                                <th class="py-3">Status</th>
                                                <th class="py-3 text-end px-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($orders as $order)
                                                @php
                                                    $orderData = $order->invoice_data;
                                                    if (is_string($orderData)) {
                                                        $orderData = json_decode($orderData, true);
                                                    }
                                                    $cartItems = $orderData['cart_items'] ?? [];
                                                    $orderTotal = $orderData['total'] ?? $order->total_amount;
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-3">
                                                        <span class="fw-medium">{{ $order->invoice_number }}</span>
                                                    </td>
                                                    <td class="py-3">{{ $order->created_at->format('M d, Y') }}</td>
                                                    <td class="py-3">{{ count($cartItems) }} items</td>
                                                    <td class="py-3 fw-bold">₹{{ number_format($orderTotal, 2) }}</td>
                                                    <td class="py-3">
                                                        @php
                                                            $statusColors = [
                                                                'Draft' => 'secondary',
                                                                'Approved' => 'info',
                                                                'Dispatch' => 'primary',
                                                                'Out for Delivery' => 'warning',
                                                                'Delivered' => 'success',
                                                                'Return' => 'danger',
                                                            ];
                                                        @endphp
                                                        <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                            {{ $order->status }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 text-end px-4">
                                                        <a href="{{ route('vendor.invoices.show', $order->id) }}" 
                                                           class="btn btn-sm btn-outline-primary rounded-pill">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-shopping-bag fa-3x mb-3"></i>
                                                            <p class="mb-0">No orders yet</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            @if($orders->hasPages())
                                <div class="card-footer bg-white border-0">
                                    {{ $orders->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>
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
