@extends('frontend.layouts.app')

@section('title', 'Subscription Plans')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center mb-4">
                <h1 class="h2 fw-bold mb-3">Choose Your Subscription Plan</h1>
                <p class="text-muted">Select the perfect plan for your business needs</p>
            </div>
            
            @if($currentSubscription && $currentSubscription->isActive())
                <div class="alert alert-info border-0 shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle fa-lg me-3 mt-1"></i>
                        <div>
                            <strong>Current Subscription:</strong> You are subscribed to <strong>{{ $currentSubscription->plan->name }}</strong> plan.
                            @if($currentSubscription->ends_at)
                                Your subscription will expire on <strong>{{ $currentSubscription->ends_at->format('M d, Y') }}</strong>.
                            @endif
                            <a href="{{ route('vendor.subscription.current') }}" class="alert-link ms-2">View Details</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning border-0 shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle fa-lg me-3 mt-1"></i>
                        <div>
                            <strong>No Active Subscription:</strong> Please purchase a subscription plan to access all vendor panel features.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="row justify-content-center">
        @foreach($plans as $plan)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow {{ $plan->is_featured ? 'border-theme' : '' }}" style="{{ $plan->is_featured ? 'border-width: 2px !important; border-style: solid !important;' : '' }}">
                    @if($plan->is_featured)
                        <div class="card-header bg-theme text-white text-center py-2 border-0">
                            <i class="fas fa-star me-1"></i> Most Popular
                        </div>
                    @endif
                    
                    <div class="card-body d-flex flex-column text-center">
                        <h4 class="card-title mb-3 fw-bold">{{ $plan->name }}</h4>
                        
                        <div class="mb-4">
                            <h2 class="display-4 mb-0 fw-bold">
                                @if($plan->price == 0)
                                    <span class="text-success">Free</span>
                                @else
                                    <span class="text-theme">₹{{ number_format($plan->price, 0) }}</span>
                                @endif
                            </h2>
                            <small class="text-muted">
                                per {{ $plan->billing_cycle }}
                                @if($plan->duration_days)
                                    <br>({{ $plan->duration_days }} days)
                                @endif
                            </small>
                        </div>

                        @if($plan->description)
                            <p class="text-muted small mb-3">{{ $plan->description }}</p>
                        @endif

                        @if($plan->trial_days > 0)
                            <div class="alert alert-success py-2 mb-3 border-0">
                                <i class="fas fa-gift me-1"></i> {{ $plan->trial_days }} days free trial
                            </div>
                        @endif

                        <ul class="list-unstyled mb-4 flex-grow-1 text-start">
                            @if($plan->features)
                                @foreach($plan->features as $feature)
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>

                        <div class="mt-auto">
                            @if($currentSubscription && $currentSubscription->plan_id == $plan->id && $currentSubscription->isActive())
                                <button class="btn btn-secondary w-100 rounded-pill" disabled>
                                    <i class="fas fa-check me-1"></i> Current Plan
                                </button>
                            @else
                                @if($plan->price == 0)
                                    <button class="btn btn-outline-theme w-100 rounded-pill" onclick="subscribeToPlan({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price }})">
                                        <i class="fas fa-gift me-1"></i> Get Started Free
                                    </button>
                                @else
                                    <button class="btn btn-theme w-100 rounded-pill" onclick="subscribeToPlan({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price }})">
                                        <i class="fas fa-shopping-cart me-1"></i> Subscribe Now
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($plans->isEmpty())
        <div class="alert alert-info text-center border-0 shadow-sm">
            <i class="fas fa-info-circle me-2"></i>
            No subscription plans are currently available. Please contact the administrator.
        </div>
    @endif
</div>

<!-- Razorpay Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Processing Payment</h5>
            </div>
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-theme mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">Please wait while we process your payment...</p>
            </div>
        </div>
    </div>
</div>

<!-- Referral Code Modal (First Time Purchase) -->
@if($isFirstTimePurchase)
<div class="modal fade" id="referralModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-theme text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-gift me-2"></i>Do You Have a Referral Code?
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    If you have a referral code from another vendor, enter it below to get a <strong class="text-success">10% discount</strong> on your subscription!
                </p>
                
                <div class="mb-3">
                    <label for="referralCodeInput" class="form-label fw-semibold">Referral Code (Optional)</label>
                    <input type="text" 
                           class="form-control rounded-pill" 
                           id="referralCodeInput" 
                           placeholder="Enter referral code (e.g., STORENAME-1234)">
                    <div class="invalid-feedback" id="referralCodeError"></div>
                </div>
                
                <!-- Discount Preview -->
                <div id="discountPreview" class="alert alert-success border-0 d-none">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Great!</strong> You'll save <span id="discountAmount">₹0</span> (10% off) on this plan!
                </div>
                
                <div class="alert alert-info border-0 small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    This is optional. You can skip this step if you don't have a referral code.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill" onclick="proceedWithoutReferral()">
                    Skip
                </button>
                <button type="button" class="btn btn-theme rounded-pill" onclick="proceedWithReferral()">
                    <i class="fas fa-check me-1"></i>Continue
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    const razorpayKeyId = '{{ $razorpayKeyId }}';
    const isFirstTimePurchase = {{ $isFirstTimePurchase ? 'true' : 'false' }};
    let paymentModal;
    let referralModal;
    let currentPlanId, currentPlanName, currentPlanPrice;

    document.addEventListener('DOMContentLoaded', function() {
        // Check if Razorpay is loaded
        if (typeof Razorpay === 'undefined') {
            console.error('Razorpay script not loaded!');
        } else {
            console.log('Razorpay loaded successfully');
        }
        
        paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        @if($isFirstTimePurchase)
        referralModal = new bootstrap.Modal(document.getElementById('referralModal'));
        
        // Real-time discount preview
        document.getElementById('referralCodeInput').addEventListener('input', function(e) {
            const code = e.target.value.trim();
            const discountPreview = document.getElementById('discountPreview');
            const discountAmount = document.getElementById('discountAmount');
            
            if (code.length > 0 && currentPlanPrice > 0) {
                const discount = currentPlanPrice * 0.10;
                discountAmount.textContent = '₹' + discount.toFixed(2);
                discountPreview.classList.remove('d-none');
            } else {
                discountPreview.classList.add('d-none');
            }
        });
        @endif
    });

    function subscribeToPlan(planId, planName, planPrice) {
        // Store plan details for later use
        currentPlanId = planId;
        currentPlanName = planName;
        currentPlanPrice = planPrice;

        // If first time purchase, show referral modal first
        if (isFirstTimePurchase) {
            referralModal.show();
        } else {
            // Proceed directly to payment
            initiatePayment(null);
        }
    }

    function proceedWithoutReferral() {
        referralModal.hide();
        initiatePayment(null);
    }

    function proceedWithReferral() {
        const referralCode = document.getElementById('referralCodeInput').value.trim();
        const errorDiv = document.getElementById('referralCodeError');
        const inputField = document.getElementById('referralCodeInput');
        
        // Clear previous errors
        inputField.classList.remove('is-invalid');
        errorDiv.textContent = '';

        referralModal.hide();
        initiatePayment(referralCode || null);
    }

    function initiatePayment(referralCode) {
        // Show loading modal
        paymentModal.show();

        // Prepare request data
        const requestData = {
            plan_id: currentPlanId
        };

        if (referralCode) {
            requestData.referral_code = referralCode;
        }

        console.log('Sending request data:', requestData);

        // Create Razorpay order
        fetch('{{ route("vendor.subscription.subscribe") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json().then(data => {
                console.log('API Response:', data);
                if (!response.ok) {
                    // Return the error data with success: false
                    return { success: false, message: data.message || `HTTP error! status: ${response.status}`, data: data };
                }
                return data;
            });
        })
        .then(data => {
            paymentModal.hide();

            if (!data.success) {
                console.error('API Error:', data);
                
                // Check if there are validation errors
                if (data.errors) {
                    console.error('Validation errors:', data.errors);
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    showAlert('error', errorMessages);
                    return;
                }
                
                // If referral code error, show the modal again with error
                if (referralCode && data.message && data.message.toLowerCase().includes('referral')) {
                    if (isFirstTimePurchase) {
                        const errorDiv = document.getElementById('referralCodeError');
                        const inputField = document.getElementById('referralCodeInput');
                        inputField.classList.add('is-invalid');
                        errorDiv.textContent = data.message;
                        referralModal.show();
                        return;
                    }
                }
                showAlert('error', data.message || 'Failed to create order');
                return;
            }

            // Show discount message if applied
            if (data.discount_percentage > 0) {
                showAlert('success', `Discount Applied! You saved ₹${data.discount_applied.toFixed(2)} (${data.discount_percentage}% off)`);
            }

            // Free plan - no payment required
            if (currentPlanPrice == 0) {
                verifyPayment({
                    razorpay_order_id: data.order_id,
                    razorpay_payment_id: 'free_plan',
                    razorpay_signature: 'free_plan',
                    subscription_id: data.subscription_id
                });
                return;
            }

            // Paid plan - open Razorpay checkout
            console.log('Opening Razorpay with options:', {
                key: razorpayKeyId,
                amount: data.amount * 100,
                order_id: data.order_id
            });
            
            const options = {
                key: razorpayKeyId,
                amount: data.amount * 100,
                currency: data.currency,
                name: 'Subscription Payment',
                description: currentPlanName + ' Plan',
                order_id: data.order_id,
                prefill: {
                    name: data.vendor_name,
                    email: data.vendor_email,
                    contact: data.vendor_phone
                },
                theme: {
                    color: getComputedStyle(document.documentElement).getPropertyValue('--theme-color').trim() || '#FF6B00'
                },
                handler: function(response) {
                    verifyPayment({
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature,
                        subscription_id: data.subscription_id
                    });
                },
                modal: {
                    ondismiss: function() {
                        showAlert('info', 'Payment cancelled');
                    }
                }
            };

            if (typeof Razorpay === 'undefined') {
                console.error('Razorpay is not defined!');
                showAlert('error', 'Payment gateway not loaded. Please refresh the page.');
                return;
            }

            const rzp = new Razorpay(options);
            console.log('Razorpay instance created:', rzp);
            rzp.open();
            console.log('Razorpay.open() called');
        })
        .catch(error => {
            paymentModal.hide();
            console.error('Error:', error);
            showAlert('error', 'Failed to initiate payment. Please try again.');
        });
    }

    function verifyPayment(paymentData) {
        paymentModal.show();

        fetch('{{ route("vendor.subscription.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(paymentData)
        })
        .then(response => response.json())
        .then(data => {
            paymentModal.hide();

            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => {
                    window.location.href = data.redirect_url || '{{ route("vendor.dashboard") }}';
                }, 1500);
            } else {
                showAlert('error', data.message || 'Payment verification failed');
            }
        })
        .catch(error => {
            paymentModal.hide();
            console.error('Error:', error);
            showAlert('error', 'Failed to verify payment. Please contact support.');
        });
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          type === 'info' ? 'alert-info' : 'alert-warning';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        const container = document.querySelector('.container');
        if (container) {
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        } else {
            // Fallback to console if no container found
            console.log(`Alert (${type}):`, message);
        }
    }
</script>
@endsection

@section('styles')
<style>
/* Subscription Plans Page Styling */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15) !important;
}

.display-4 {
    font-weight: 700;
}

.list-unstyled li {
    padding: 0.25rem 0;
}

#referralCodeInput {
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 1px;
}

/* Theme color classes */
.bg-theme {
    background-color: var(--theme-color, #FF6B00) !important;
}

.text-theme {
    color: var(--theme-color, #FF6B00) !important;
}

.border-theme {
    border-color: var(--theme-color, #FF6B00) !important;
}

.btn-theme {
    background-color: var(--theme-color, #FF6B00);
    border-color: var(--theme-color, #FF6B00);
    color: #fff;
}

.btn-theme:hover {
    background-color: color-mix(in srgb, var(--theme-color, #FF6B00) 90%, black);
    border-color: color-mix(in srgb, var(--theme-color, #FF6B00) 90%, black);
    color: #fff;
}

.btn-outline-theme {
    border-color: var(--theme-color, #FF6B00);
    color: var(--theme-color, #FF6B00);
    background-color: transparent;
}

.btn-outline-theme:hover {
    background-color: var(--theme-color, #FF6B00);
    border-color: var(--theme-color, #FF6B00);
    color: #fff;
}

/* Featured plan styling */
.border-theme {
    position: relative;
}

.card.border-theme::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(135deg, var(--theme-color, #FF6B00), color-mix(in srgb, var(--theme-color, #FF6B00) 70%, cyan));
    border-radius: 0.375rem;
    z-index: -1;
}
</style>
@endsection
