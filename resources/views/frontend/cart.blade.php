@extends('frontend.layouts.app')

@section('title', 'Shopping Cart - ' . setting('site_title', 'Frontend App'))

@section('content')
<div class="container">
    <nav aria-label="breadcrumb" class="my-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4 heading-text">
                <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
            </h1>
        </div>
    </div>
    
    @if($cartItems->count() > 0)
    <div class="row">
        <div class="col-lg-8">
            <!-- Cart Items Section -->
            <div class="card shadow-sm border-0 mb-4 hover-lift">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cartItems as $index => $item)
                                <tr data-cart-item-id="{{ $item->id }}" class="cart-item-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->product->mainPhoto)
                                                <img src="{{ $item->product->mainPhoto->url }}" class="img-fluid rounded me-3 product-thumbnail" alt="{{ $item->product->name }}" style="width: 80px; height: 80px; object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center rounded me-3" style="width: 80px; height: 80px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="{{ route('frontend.product.show', $item->product->slug) }}" class="product-link text-decoration-none">
                                                        {{ $item->product->name }}
                                                    </a>
                                                </h6>
                                                @if($item->variation)
                                                    <div class="mb-1">
                                                        <small class="text-muted">
                                                            <strong>{{ $item->variation->display_name }}</strong>
                                                        </small>
                                                    </div>
                                                @endif
                                                <small class="text-muted">{{ Str::limit($item->product->description ?? 'No description', 50) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="fw-bold text-success mb-0 price-tag">₹{{ number_format($item->price, 2) }}</p>
                                    </td>
                                    <td>
                                        @php
                                            // Get actual stock - for variations use variation stock, for simple products use product stock
                                            $actualStock = $item->variation ? $item->variation->stock_quantity : $item->product->stock_quantity;
                                        @endphp
                                        <div class="input-group quantity-control" style="width: 120px;">
                                            <button class="btn btn-outline-theme decrement-qty btn-ripple" type="button">-</button>
                                            <input type="number" class="form-control text-center qty-input" value="{{ $item->quantity }}" min="1" data-max="{{ $actualStock }}">
                                            <button class="btn btn-outline-theme increment-qty btn-ripple" type="button">+</button>
                                        </div>
                                        @if($actualStock < 10 && $actualStock > 0)
                                            <small class="text-warning stock-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Only {{ $actualStock }} left in stock
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <p class="fw-bold mb-0 item-total price-tag">₹{{ number_format($item->price * $item->quantity, 2) }}</p>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger remove-item btn-ripple hover-scale" data-id="{{ $item->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Personal Details and Shipping Address Section -->
            <div class="card shadow-sm border-0 mb-4 hover-lift">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0 fw-bold heading-text">Personal Details & Shipping Address</h5>
                </div>
                <div class="card-body">
                    @auth
                    <form action="{{ route('frontend.profile.update') }}" method="POST">
                        @csrf
                        @method('POST')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-medium label-text">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-animated" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-medium label-text">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-animated" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="mobile_number" class="form-label fw-medium label-text">Mobile Number</label>
                                <input type="text" class="form-control form-control-animated" id="mobile_number" name="mobile_number" value="{{ old('mobile_number', Auth::user()->mobile_number) }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label fw-medium label-text">Date of Birth</label>
                                <input type="date" class="form-control form-control-animated" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', Auth::user()->date_of_birth ? Auth::user()->date_of_birth->format('Y-m-d') : '') }}">
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="address" class="form-label fw-medium label-text">Shipping Address</label>
                                <textarea class="form-control form-control-animated" id="address" name="address" rows="3" placeholder="Enter your complete shipping address">{{ old('address', Auth::user()->address) }}</textarea>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-theme rounded-pill px-4 btn-ripple hover-lift">
                                <i class="fas fa-save me-2"></i>Update Details
                            </button>
                        </div>
                    </form>
                    @else
                    <form id="guest-details-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guest_name" class="form-label fw-medium label-text">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-animated" id="guest_name" name="name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="guest_email" class="form-label fw-medium label-text">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-animated" id="guest_email" name="email" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="guest_mobile_number" class="form-label fw-medium label-text">Mobile Number</label>
                                <input type="text" class="form-control form-control-animated" id="guest_mobile_number" name="mobile_number">
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="guest_address" class="form-label fw-medium label-text">Shipping Address</label>
                                <textarea class="form-control form-control-animated" id="guest_address" name="address" rows="3" placeholder="Enter your complete shipping address"></textarea>
                            </div>
                        </div>
                    </form>
                    @endauth
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Coupon Code Section -->
            <div class="card shadow-sm border-0 mb-4 hover-lift">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-ticket-alt me-2"></i>Have a Coupon?</h5>
                    
                    <!-- Coupon Input Form -->
                    <div id="coupon-input-section">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-animated" id="coupon-code-input" placeholder="Enter coupon code" maxlength="50">
                            <button class="btn btn-theme btn-ripple" type="button" id="apply-coupon-btn">
                                <span class="btn-text">Apply</span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                </span>
                            </button>
                        </div>
                        <div id="coupon-error" class="text-danger small mt-2 d-none"></div>
                    </div>
                    
                    <!-- Applied Coupon Display -->
                    <div id="applied-coupon-section" class="d-none">
                        <div class="d-flex justify-content-between align-items-center bg-success text-white rounded p-3">
                            <div>
                                <span class="badge bg-white text-success me-2"><i class="fas fa-check-circle me-1"></i>Applied</span>
                                <span class="fw-bold coupon-code-display"></span>
                                <div class="small text-white coupon-discount-display"></div>
                            </div>
                            <button class="btn btn-sm btn-outline-danger btn-ripple" type="button" id="remove-coupon-btn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary Section -->
            <div class="card shadow-sm border-0 mb-4 hover-lift order-summary-card">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    
                    <!-- Price Breakdown -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2 summary-row">
                            <span>Subtotal:</span>
                            <span class="fw-bold cart-subtotal">₹{{ number_format($total, 2) }}</span>
                        </div>
                        
                        <!-- Coupon Discount Row (hidden by default) -->
                        <div class="d-flex justify-content-between mb-2 summary-row coupon-discount-row d-none">
                            <span class="text-success">
                                <i class="fas fa-tag me-1"></i>Coupon Discount:
                            </span>
                            <span class="fw-bold text-success coupon-discount-amount">-₹0.00</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2 summary-row">
                            <span>Shipping:</span>
                            <span class="fw-bold text-success">Free</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 summary-row">
                            <span>Tax:</span>
                            <span class="fw-bold">₹0.00</span>
                        </div>
                    </div>
                    
                    <hr class="animated-hr">
                    
                    <!-- Total -->
                    <div class="d-flex justify-content-between mb-4 total-row">
                        <h5>Total:</h5>
                        <h5 class="fw-bold cart-total text-success">₹{{ number_format($total, 2) }}</h5>
                    </div>
                </div>
            </div>
            
            <!-- Payment Options Section -->
            <div class="card shadow-sm border-0 hover-lift">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="fas fa-credit-card me-2"></i>Payment Options</h5>
                    
                    <!-- Online Payment -->
                    @if(show_online_payment())
                    <button class="btn btn-theme w-100 mb-3 d-flex align-items-center justify-content-center btn-ripple hover-lift payment-btn" id="online-payment">
                        <i class="fas fa-credit-card me-2"></i>Online Payment
                    </button>
                    @endif
                    
                    <!-- Cash on Delivery -->
                    @if(show_cod_payment())
                    <button class="btn btn-outline-theme w-100 mb-3 d-flex align-items-center justify-content-center btn-ripple hover-lift payment-btn" id="cod-payment">
                        <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery
                    </button>
                    @endif
                    
                    <!-- Send Proforma Invoice -->
                    @if(show_invoice_payment())
                    <button class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center mb-2 btn-ripple hover-lift payment-btn" id="invoice-payment">
                        <i class="fas fa-file-invoice me-2"></i>Send Proforma Invoice
                    </button>
                    @endif
                    
                    <a href="{{ route('frontend.home') }}" class="btn btn-link w-100 mt-3 continue-shopping">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 empty-cart-card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3 empty-cart-icon"></i>
                    <h3 class="mb-3">Your cart is empty</h3>
                    <p class="mb-4">Looks like you haven't added any items to your cart yet.</p>
                    <a href="{{ route('frontend.home') }}" class="btn btn-theme btn-ripple hover-lift">
                        <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    /* Cart item styles */
    .cart-item-row {
        opacity: 1;
    }
    
    /* Product thumbnail hover */
    .product-thumbnail {
    }
    
    .product-thumbnail:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    /* Quantity control */
    .quantity-control .btn {
    }
    
    .qty-input {
        /* Hide number input arrows/spinners */
        -moz-appearance: textfield;
    }
    
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    .qty-input:focus {
        border-color: var(--theme-color);
        box-shadow: 0 0 0 0.2rem rgba(var(--theme-color-rgb), 0.25);
    }
    
    /* Price tag */
    .price-tag {
    }
    
    /* Stock warning */
    .stock-warning {
    }
    
    /* Form control */
    .form-control-animated {
    }
    
    .form-control-animated:focus {
        border-color: var(--theme-color);
        box-shadow: 0 0 0 0.2rem rgba(var(--theme-color-rgb), 0.15);
    }
    
    /* Order summary */
    .order-summary-card {
        position: sticky;
        top: 100px;
    }
    
    .summary-row {
        padding: 5px;
        border-radius: 5px;
    }
    
    .summary-row:hover {
        background-color: rgba(var(--theme-color-rgb), 0.05);
    }
    
    .total-row {
    }
    
    .animated-hr {
        border: none;
        height: 2px;
        background: var(--theme-color);
    }
    
    /* Payment button */
    .payment-btn {
    }
    
    .payment-btn:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    
    .payment-btn i {
    }
    
    /* Continue shopping link */
    .continue-shopping {
    }
    
    /* Empty cart */
    .empty-cart-card {
    }
    
    .empty-cart-icon {
    }
    
    /* Remove button */
    .remove-item {
    }
    
    /* Table styling */
    .table td, .table th {
        vertical-align: middle;
    }
    
    /* Product link styles */
    .product-link {
        color: var(--theme-color);
        font-weight: 600;
    }
    
    .product-link:hover {
        color: var(--link-hover-color);
        text-decoration: underline !important;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .order-summary-card {
            position: static;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle quantity increment
    document.querySelectorAll('.increment-qty').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('.qty-input');
            const currentValue = parseInt(input.value);
            const maxValue = parseInt(input.dataset.max);
            
            if (currentValue < maxValue) {
                input.value = currentValue + 1;
                updateCartItem(input.closest('tr').dataset.cartItemId, input.value);
            }
        });
    });
    
    // Handle quantity decrement
    document.querySelectorAll('.decrement-qty').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('.qty-input');
            const currentValue = parseInt(input.value);
            
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateCartItem(input.closest('tr').dataset.cartItemId, input.value);
            }
        });
    });
    
    // Handle quantity input change
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function() {
            const maxValue = parseInt(this.dataset.max);
            let value = parseInt(this.value);
            
            if (isNaN(value) || value < 1) {
                value = 1;
            } else if (value > maxValue) {
                value = maxValue;
                showToast(`Only ${maxValue} items available in stock.`, 'warning');
            }
            
            this.value = value;
            updateCartItem(this.closest('tr').dataset.cartItemId, value);
        });
    });
    
    // Handle remove item
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.id;
            const row = document.querySelector(`tr[data-cart-item-id="${itemId}"]`);
            
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }
            
            removeCartItem(itemId);
        });
    });
    
    // Payment option handlers
    const onlinePaymentBtn = document.getElementById('online-payment');
    if (onlinePaymentBtn) {
        onlinePaymentBtn.addEventListener('click', function() {
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            setTimeout(() => {
                alert('Online payment functionality would be implemented here. Redirecting to payment gateway...');
                this.innerHTML = '<i class="fas fa-credit-card me-2"></i>Online Payment';
            }, 1000);
        });
    }
    
    const codPaymentBtn = document.getElementById('cod-payment');
    if (codPaymentBtn) {
        codPaymentBtn.addEventListener('click', function() {
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            setTimeout(() => {
                alert('Cash on Delivery selected. Your order will be processed for delivery.');
                this.innerHTML = '<i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery';
            }, 1000);
        });
    }
    
    const invoicePaymentBtn = document.getElementById('invoice-payment');
    if (invoicePaymentBtn) {
        invoicePaymentBtn.addEventListener('click', function() {
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
            window.location.href = '{{ route("frontend.cart.proforma.invoice") }}';
        });
    }
    
    // Function to update cart item
    function updateCartItem(itemId, quantity) {
        if (document.querySelector('meta[name="csrf-token"]')) {
            fetch(`{{ url('/cart/update') }}/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quantity: quantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`tr[data-cart-item-id="${itemId}"]`);
                    
                    // Update the total
                    const itemTotal = row.querySelector('.item-total');
                    itemTotal.textContent = '₹' + data.item_total;
                    
                    // Update cart totals
                    document.querySelectorAll('.cart-subtotal, .cart-total').forEach(el => {
                        el.textContent = '₹' + data.cart_total;
                    });
                    
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred while updating the cart.', 'error');
            });
        }
    }
    
    // Function to remove cart item
    function removeCartItem(itemId) {
        if (document.querySelector('meta[name="csrf-token"]')) {
            fetch(`{{ url('/cart/remove') }}/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`tr[data-cart-item-id="${itemId}"]`).remove();
                    
                    document.querySelectorAll('.cart-subtotal, .cart-total').forEach(el => {
                        el.textContent = '₹' + data.cart_total;
                    });
                    
                    updateCartCount(data.cart_count);
                    showToast(data.message, 'success');
                    
                    if (data.cart_count === 0) {
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred while removing the item.', 'error');
            });
        }
    }
    
    // Function to update cart count
    function updateCartCount(count) {
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = count;
            cartCountElement.classList.add('pulse');
            setTimeout(() => cartCountElement.classList.remove('pulse'), 500);
            if (count > 0) {
                cartCountElement.classList.remove('d-none');
            } else {
                cartCountElement.classList.add('d-none');
            }
        }
    }

    // ==================== COUPON FUNCTIONALITY ====================
    
    const couponInputSection = document.getElementById('coupon-input-section');
    const appliedCouponSection = document.getElementById('applied-coupon-section');
    const couponCodeInput = document.getElementById('coupon-code-input');
    const applyCouponBtn = document.getElementById('apply-coupon-btn');
    const removeCouponBtn = document.getElementById('remove-coupon-btn');
    const couponError = document.getElementById('coupon-error');
    const couponDiscountRow = document.querySelector('.coupon-discount-row');
    const couponDiscountAmount = document.querySelector('.coupon-discount-amount');
    const couponCodeDisplay = document.querySelector('.coupon-code-display');
    const couponDiscountDisplay = document.querySelector('.coupon-discount-display');
    
    // Store original subtotal for calculations
    let originalSubtotal = {{ $total }};
    let appliedCouponData = null;
    
    // Check for existing applied coupon on page load
    checkAppliedCoupon();
    
    // Apply coupon button click handler
    if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', function() {
            applyCoupon();
        });
    }
    
    // Apply coupon on Enter key
    if (couponCodeInput) {
        couponCodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCoupon();
            }
        });
    }
    
    // Remove coupon button click handler
    if (removeCouponBtn) {
        removeCouponBtn.addEventListener('click', function() {
            removeCoupon();
        });
    }
    
    // Function to check for existing applied coupon
    function checkAppliedCoupon() {
        fetch('{{ route("frontend.cart.coupon.applied") }}', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.coupon) {
                appliedCouponData = data.coupon;
                showAppliedCoupon(data.coupon);
            }
        })
        .catch(error => {
            console.error('Error checking applied coupon:', error);
        });
    }
    
    // Function to apply coupon
    function applyCoupon() {
        const code = couponCodeInput.value.trim();
        
        if (!code) {
            showCouponError('Please enter a coupon code.');
            return;
        }
        
        // Show loading state
        applyCouponBtn.querySelector('.btn-text').classList.add('d-none');
        applyCouponBtn.querySelector('.btn-loading').classList.remove('d-none');
        applyCouponBtn.disabled = true;
        hideCouponError();
        
        fetch('{{ route("frontend.cart.coupon.apply") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ coupon_code: code })
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            applyCouponBtn.querySelector('.btn-text').classList.remove('d-none');
            applyCouponBtn.querySelector('.btn-loading').classList.add('d-none');
            applyCouponBtn.disabled = false;
            
            if (data.success) {
                appliedCouponData = data.coupon;
                showAppliedCoupon(data.coupon);
                updateCartTotals(data.cart_subtotal, data.cart_total, data.coupon.discount_amount);
                showToast(data.message, 'success');
                couponCodeInput.value = '';
            } else {
                showCouponError(data.message);
            }
        })
        .catch(error => {
            // Reset button state
            applyCouponBtn.querySelector('.btn-text').classList.remove('d-none');
            applyCouponBtn.querySelector('.btn-loading').classList.add('d-none');
            applyCouponBtn.disabled = false;
            
            showCouponError('An error occurred. Please try again.');
            console.error('Error applying coupon:', error);
        });
    }
    
    // Function to remove coupon
    function removeCoupon() {
        fetch('{{ route("frontend.cart.coupon.remove") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                appliedCouponData = null;
                hideAppliedCoupon();
                
                // Update totals - remove discount
                document.querySelectorAll('.cart-total').forEach(el => {
                    el.textContent = '₹' + data.cart_total;
                });
                
                // Hide coupon discount row
                if (couponDiscountRow) {
                    couponDiscountRow.classList.add('d-none');
                }
                
                showToast(data.message, 'success');
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
            console.error('Error removing coupon:', error);
        });
    }
    
    // Function to show applied coupon
    function showAppliedCoupon(coupon) {
        if (couponInputSection) couponInputSection.classList.add('d-none');
        if (appliedCouponSection) appliedCouponSection.classList.remove('d-none');
        if (couponCodeDisplay) couponCodeDisplay.textContent = coupon.code;
        if (couponDiscountDisplay) couponDiscountDisplay.textContent = coupon.discount_display;
        
        // Show discount row in order summary
        if (couponDiscountRow) {
            couponDiscountRow.classList.remove('d-none');
            if (couponDiscountAmount) {
                couponDiscountAmount.textContent = '-₹' + parseFloat(coupon.discount_amount).toFixed(2);
            }
        }
    }
    
    // Function to hide applied coupon
    function hideAppliedCoupon() {
        if (couponInputSection) couponInputSection.classList.remove('d-none');
        if (appliedCouponSection) appliedCouponSection.classList.add('d-none');
    }
    
    // Function to update cart totals
    function updateCartTotals(subtotal, total, discountAmount) {
        document.querySelectorAll('.cart-subtotal').forEach(el => {
            el.textContent = '₹' + subtotal;
        });
        document.querySelectorAll('.cart-total').forEach(el => {
            el.textContent = '₹' + total;
        });
        
        // Update discount row
        if (couponDiscountRow && discountAmount > 0) {
            couponDiscountRow.classList.remove('d-none');
            if (couponDiscountAmount) {
                couponDiscountAmount.textContent = '-₹' + parseFloat(discountAmount).toFixed(2);
            }
        }
    }
    
    // Function to show coupon error
    function showCouponError(message) {
        if (couponError) {
            couponError.textContent = message;
            couponError.classList.remove('d-none');
        }
    }
    
    // Function to hide coupon error
    function hideCouponError() {
        if (couponError) {
            couponError.classList.add('d-none');
        }
    }
    
    // Update cart totals when items are updated (override existing function)
    const originalUpdateCartItem = window.updateCartItem;
    function updateCartItemWithCoupon(itemId, quantity) {
        if (document.querySelector('meta[name="csrf-token"]')) {
            fetch(`{{ url('/cart/update') }}/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quantity: quantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`tr[data-cart-item-id="${itemId}"]`);
                    
                    // Update the item total
                    const itemTotal = row.querySelector('.item-total');
                    itemTotal.textContent = '₹' + data.item_total;
                    
                    // Update subtotal
                    originalSubtotal = parseFloat(data.cart_total.replace(/,/g, ''));
                    document.querySelectorAll('.cart-subtotal').forEach(el => {
                        el.textContent = '₹' + data.cart_total;
                    });
                    
                    // Recalculate with coupon if applied
                    if (appliedCouponData) {
                        recalculateCouponDiscount();
                    } else {
                        document.querySelectorAll('.cart-total').forEach(el => {
                            el.textContent = '₹' + data.cart_total;
                        });
                    }
                    
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred while updating the cart.', 'error');
            });
        }
    }
    
    // Recalculate coupon discount after cart changes
    function recalculateCouponDiscount() {
        fetch('{{ route("frontend.cart.coupon.applied") }}', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.coupon) {
                appliedCouponData = data.coupon;
                showAppliedCoupon(data.coupon);
                
                // Calculate new total
                const newTotal = originalSubtotal - parseFloat(data.coupon.discount_amount);
                document.querySelectorAll('.cart-total').forEach(el => {
                    el.textContent = '₹' + newTotal.toFixed(2);
                });
            } else if (data.message) {
                // Coupon no longer valid
                hideAppliedCoupon();
                if (couponDiscountRow) couponDiscountRow.classList.add('d-none');
                document.querySelectorAll('.cart-total').forEach(el => {
                    el.textContent = '₹' + originalSubtotal.toFixed(2);
                });
                showToast(data.message, 'warning');
            }
        })
        .catch(error => {
            console.error('Error recalculating coupon:', error);
        });
    }
});
</script>

<style>
    .bounce {
    }
</style>
@endsection
