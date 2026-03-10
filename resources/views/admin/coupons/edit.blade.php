@extends('admin.layouts.app')

@section('title', 'Edit Coupon')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Edit Coupon',
                'breadcrumbs' => [
                    'Coupons' => route('admin.coupons.index'),
                    'Edit' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h4 class="card-title mb-0 fw-bold">Edit Coupon: {{ $coupon->code }}</h4>
                                <p class="mb-0 text-muted">Update coupon details</p>
                            </div>
                            
                            <div class="card-body">
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
                                
                                <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <!-- Coupon Code -->
                                        <div class="col-md-6 mb-3">
                                            <label for="code" class="form-label fw-medium">Coupon Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control text-uppercase" id="code" name="code" 
                                                   value="{{ old('code', $coupon->code) }}" required placeholder="e.g., SAVE20">
                                            <small class="text-muted">Unique code customers will enter at checkout</small>
                                        </div>
                                        
                                        <!-- Description -->
                                        <div class="col-md-6 mb-3">
                                            <label for="description" class="form-label fw-medium">Description</label>
                                            <input type="text" class="form-control" id="description" name="description" 
                                                   value="{{ old('description', $coupon->description) }}" placeholder="e.g., Summer Sale Discount">
                                        </div>
                                        
                                        <!-- Discount Type -->
                                        <div class="col-md-6 mb-3">
                                            <label for="discount_type" class="form-label fw-medium">Discount Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="discount_type" name="discount_type" required>
                                                <option value="percentage" {{ old('discount_type', $coupon->discount_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                                <option value="fixed" {{ old('discount_type', $coupon->discount_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Discount Value -->
                                        <div class="col-md-6 mb-3">
                                            <label for="discount_value" class="form-label fw-medium">Discount Value <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text discount-symbol">%</span>
                                                <input type="number" class="form-control" id="discount_value" name="discount_value" 
                                                       value="{{ old('discount_value', $coupon->discount_value) }}" required min="0.01" step="0.01" placeholder="10">
                                            </div>
                                        </div>
                                        
                                        <!-- Min Order Amount -->
                                        <div class="col-md-6 mb-3">
                                            <label for="min_order_amount" class="form-label fw-medium">Minimum Order Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" 
                                                       value="{{ old('min_order_amount', $coupon->min_order_amount) }}" min="0" step="0.01" placeholder="0">
                                            </div>
                                            <small class="text-muted">Leave 0 for no minimum</small>
                                        </div>
                                        
                                        <!-- Max Discount Amount (for percentage) -->
                                        <div class="col-md-6 mb-3" id="max_discount_container">
                                            <label for="max_discount_amount" class="form-label fw-medium">Maximum Discount Cap</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" class="form-control" id="max_discount_amount" name="max_discount_amount" 
                                                       value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}" min="0" step="0.01" placeholder="Optional">
                                            </div>
                                            <small class="text-muted">Maximum discount amount for percentage coupons</small>
                                        </div>
                                        
                                        <!-- Usage Limit -->
                                        <div class="col-md-6 mb-3">
                                            <label for="usage_limit" class="form-label fw-medium">Total Usage Limit</label>
                                            <input type="number" class="form-control" id="usage_limit" name="usage_limit" 
                                                   value="{{ old('usage_limit', $coupon->usage_limit) }}" min="1" placeholder="Unlimited">
                                            <small class="text-muted">Leave empty for unlimited uses (Used: {{ $coupon->usage_count }})</small>
                                        </div>
                                        
                                        <!-- Per User Limit -->
                                        <div class="col-md-6 mb-3">
                                            <label for="per_user_limit" class="form-label fw-medium">Per User Limit</label>
                                            <input type="number" class="form-control" id="per_user_limit" name="per_user_limit" 
                                                   value="{{ old('per_user_limit', $coupon->per_user_limit) }}" min="1" placeholder="1">
                                            <small class="text-muted">How many times each user can use this coupon</small>
                                        </div>
                                        
                                        <!-- Valid From -->
                                        <div class="col-md-6 mb-3">
                                            <label for="valid_from" class="form-label fw-medium">Valid From</label>
                                            <input type="datetime-local" class="form-control" id="valid_from" name="valid_from" 
                                                   value="{{ old('valid_from', $coupon->valid_from ? $coupon->valid_from->format('Y-m-d\TH:i') : '') }}">
                                            <small class="text-muted">Leave empty to start immediately</small>
                                        </div>
                                        
                                        <!-- Valid Until -->
                                        <div class="col-md-6 mb-3">
                                            <label for="valid_until" class="form-label fw-medium">Valid Until</label>
                                            <input type="datetime-local" class="form-control" id="valid_until" name="valid_until" 
                                                   value="{{ old('valid_until', $coupon->valid_until ? $coupon->valid_until->format('Y-m-d\TH:i') : '') }}">
                                            <small class="text-muted">Leave empty for no expiry</small>
                                        </div>
                                        
                                        <!-- Is Active -->
                                        <div class="col-12 mb-4">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="is_active" value="0">
                                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                       {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-medium" for="is_active">Active</label>
                                            </div>
                                            <small class="text-muted">Enable this coupon for use</small>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Update Coupon
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stats Card -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-chart-bar me-2"></i>Usage Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Total Uses:</span>
                                    <span class="fw-bold fs-5">{{ $coupon->usage_count }}</span>
                                </div>
                                @if($coupon->usage_limit)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Remaining:</span>
                                    <span class="fw-bold fs-5">{{ max(0, $coupon->usage_limit - $coupon->usage_count) }}</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    @php $usagePercent = min(100, ($coupon->usage_count / $coupon->usage_limit) * 100); @endphp
                                    <div class="progress-bar bg-theme" role="progressbar" style="width: {{ $usagePercent }}%"></div>
                                </div>
                                @endif
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Status:</span>
                                    @php $status = $coupon->status; @endphp
                                    @switch($status)
                                        @case('active')
                                            <span class="badge bg-success">Active</span>
                                            @break
                                        @case('inactive')
                                            <span class="badge bg-secondary">Inactive</span>
                                            @break
                                        @case('expired')
                                            <span class="badge bg-danger">Expired</span>
                                            @break
                                        @case('scheduled')
                                            <span class="badge bg-info">Scheduled</span>
                                            @break
                                        @case('exhausted')
                                            <span class="badge bg-warning">Exhausted</span>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const discountType = document.getElementById('discount_type');
    const discountSymbol = document.querySelector('.discount-symbol');
    const maxDiscountContainer = document.getElementById('max_discount_container');
    
    function updateDiscountUI() {
        if (discountType.value === 'percentage') {
            discountSymbol.textContent = '%';
            maxDiscountContainer.style.display = 'block';
        } else {
            discountSymbol.textContent = '₹';
            maxDiscountContainer.style.display = 'none';
        }
    }
    
    discountType.addEventListener('change', updateDiscountUI);
    updateDiscountUI();
    
    // Auto uppercase coupon code
    document.getElementById('code').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endsection
