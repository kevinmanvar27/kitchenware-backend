@extends('admin.layouts.app')

@section('title', 'Create Coupon')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Create Coupon',
                'breadcrumbs' => [
                    'Coupons' => route('admin.coupons.index'),
                    'Create' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h4 class="card-title mb-0 fw-bold">Create New Coupon</h4>
                                <p class="mb-0 text-muted">Add a new discount coupon for your customers</p>
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
                                
                                <form action="{{ route('admin.coupons.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="row">
                                        <!-- Coupon Code -->
                                        <div class="col-md-6 mb-3">
                                            <label for="code" class="form-label fw-medium">Coupon Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control text-uppercase" id="code" name="code" 
                                                   value="{{ old('code') }}" required placeholder="e.g., SAVE20">
                                            <small class="text-muted">Unique code customers will enter at checkout</small>
                                        </div>
                                        
                                        <!-- Description -->
                                        <div class="col-md-6 mb-3">
                                            <label for="description" class="form-label fw-medium">Description</label>
                                            <input type="text" class="form-control" id="description" name="description" 
                                                   value="{{ old('description') }}" placeholder="e.g., Summer Sale Discount">
                                        </div>
                                        
                                        <!-- Discount Type -->
                                        <div class="col-md-6 mb-3">
                                            <label for="discount_type" class="form-label fw-medium">Discount Type <span class="text-danger">*</span></label>
                                            <select class="form-select" id="discount_type" name="discount_type" required>
                                                <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                                <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Discount Value -->
                                        <div class="col-md-6 mb-3">
                                            <label for="discount_value" class="form-label fw-medium">Discount Value <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text discount-symbol">%</span>
                                                <input type="number" class="form-control" id="discount_value" name="discount_value" 
                                                       value="{{ old('discount_value') }}" required min="0.01" step="0.01" placeholder="10">
                                            </div>
                                        </div>
                                        
                                        <!-- Min Order Amount -->
                                        <div class="col-md-6 mb-3">
                                            <label for="min_order_amount" class="form-label fw-medium">Minimum Order Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" 
                                                       value="{{ old('min_order_amount', 0) }}" min="0" step="0.01" placeholder="0">
                                            </div>
                                            <small class="text-muted">Leave 0 for no minimum</small>
                                        </div>
                                        
                                        <!-- Max Discount Amount (for percentage) -->
                                        <div class="col-md-6 mb-3" id="max_discount_container">
                                            <label for="max_discount_amount" class="form-label fw-medium">Maximum Discount Cap</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" class="form-control" id="max_discount_amount" name="max_discount_amount" 
                                                       value="{{ old('max_discount_amount') }}" min="0" step="0.01" placeholder="Optional">
                                            </div>
                                            <small class="text-muted">Maximum discount amount for percentage coupons</small>
                                        </div>
                                        
                                        <!-- Usage Limit -->
                                        <div class="col-md-6 mb-3">
                                            <label for="usage_limit" class="form-label fw-medium">Total Usage Limit</label>
                                            <input type="number" class="form-control" id="usage_limit" name="usage_limit" 
                                                   value="{{ old('usage_limit') }}" min="1" placeholder="Unlimited">
                                            <small class="text-muted">Leave empty for unlimited uses</small>
                                        </div>
                                        
                                        <!-- Per User Limit -->
                                        <div class="col-md-6 mb-3">
                                            <label for="per_user_limit" class="form-label fw-medium">Per User Limit</label>
                                            <input type="number" class="form-control" id="per_user_limit" name="per_user_limit" 
                                                   value="{{ old('per_user_limit', 1) }}" min="1" placeholder="1">
                                            <small class="text-muted">How many times each user can use this coupon</small>
                                        </div>
                                        
                                        <!-- Valid From -->
                                        <div class="col-md-6 mb-3">
                                            <label for="valid_from" class="form-label fw-medium">Valid From</label>
                                            <input type="datetime-local" class="form-control" id="valid_from" name="valid_from" 
                                                   value="{{ old('valid_from') }}">
                                            <small class="text-muted">Leave empty to start immediately</small>
                                        </div>
                                        
                                        <!-- Valid Until -->
                                        <div class="col-md-6 mb-3">
                                            <label for="valid_until" class="form-label fw-medium">Valid Until</label>
                                            <input type="datetime-local" class="form-control" id="valid_until" name="valid_until" 
                                                   value="{{ old('valid_until') }}">
                                            <small class="text-muted">Leave empty for no expiry</small>
                                        </div>
                                        
                                        <!-- Is Active -->
                                        <div class="col-12 mb-4">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="is_active" value="0">
                                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                       {{ old('is_active', true) ? 'checked' : '' }}>
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
                                            <i class="fas fa-save me-2"></i>Create Coupon
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Help Card -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Tips</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Percentage:</strong> Use for discounts like "20% off"
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Fixed:</strong> Use for discounts like "₹100 off"
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Max Discount:</strong> Cap percentage discounts to prevent large discounts on big orders
                                    </li>
                                    <li class="mb-0">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Per User Limit:</strong> Prevent abuse by limiting uses per customer
                                    </li>
                                </ul>
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
