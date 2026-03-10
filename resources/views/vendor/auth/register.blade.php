<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vendor Registration - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
    <link href="{{ url('/css/dynamic.css') }}" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: {{ setting('theme_color', '#FF6B00') }};
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, #ffffff 100%);
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated background shapes */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        
        .shape {
            position: absolute;
            opacity: 0.1;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            background: var(--primary-color);
            border-radius: 50%;
            top: -150px;
            right: -150px;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            background: var(--primary-color);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            bottom: 10%;
            left: 5%;
            animation: float 8s ease-in-out infinite reverse;
        }
        
        .shape-3 {
            width: 150px;
            height: 150px;
            background: var(--primary-color);
            border-radius: 50%;
            top: 50%;
            right: 10%;
            animation: float 7s ease-in-out infinite;
        }
        
        .shape-4 {
            width: 250px;
            height: 250px;
            background: var(--primary-color);
            border-radius: 63% 37% 54% 46% / 55% 48% 52% 45%;
            bottom: -100px;
            right: 20%;
            animation: float 9s ease-in-out infinite;
        }
        
        .shape-5 {
            width: 180px;
            height: 180px;
            background: var(--primary-color);
            border-radius: 50%;
            top: 20%;
            left: -90px;
            animation: float 5s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(10deg);
            }
        }
        
        /* Card styling */
        .register-container {
            position: relative;
            z-index: 1;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        
        .btn-theme {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-theme:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            color: white;
        }
        
        .bg-theme {
            background: var(--primary-color);
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 0, 0.25);
        }

        /* Step Wizard Styles */
        .step-wizard {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .step-wizard::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }

        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }

        .step-item.active .step-circle {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.2);
        }

        .step-item.completed .step-circle {
            background: #28a745;
            color: white;
        }

        .step-item.completed .step-circle i {
            display: inline;
        }

        .step-label {
            font-size: 12px;
            font-weight: 500;
            color: #999;
            transition: color 0.3s ease;
        }

        .step-item.active .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }

        .step-item.completed .step-label {
            color: #28a745;
        }

        /* Form Step Content */
        .form-step {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .form-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Navigation Buttons */
        .step-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-prev, .btn-next {
            min-width: 120px;
        }

        .btn-prev {
            background: #6c757d;
            color: white;
        }

        .btn-prev:hover {
            background: #5a6268;
            color: white;
        }

        /* Progress Bar */
        .progress-bar-wrapper {
            margin-bottom: 30px;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
            background: #e0e0e0;
        }

        .progress-bar {
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .step-label {
                font-size: 10px;
            }
            
            .step-circle {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body class="min-vh-100 d-flex align-items-center py-5">
    <!-- Animated Background Shapes -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        <div class="shape shape-5"></div>
    </div>
    
    <div class="container register-container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-9">
                <div class="card register-card shadow-sm border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            @if(setting('header_logo'))
                                <img src="{{ asset('storage/' . setting('header_logo')) }}" alt="{{ setting('site_title', 'Vendor Panel') }}" class="mb-3 rounded" height="60">
                            @else
                                <div class="bg-theme rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-store text-white"></i>
                                </div>
                            @endif
                            <h1 class="h2 fw-bold">Become a Vendor</h1>
                            <p class="text-secondary">Register your store and start selling</p>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress-bar-wrapper">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 25%;" id="progressBar"></div>
                            </div>
                        </div>

                        <!-- Step Wizard -->
                        <div class="step-wizard">
                            <div class="step-item active" data-step="1">
                                <div class="step-circle">
                                    <span class="step-number">1</span>
                                    <i class="fas fa-check d-none"></i>
                                </div>
                                <div class="step-label">Personal Info</div>
                            </div>
                            <div class="step-item" data-step="2">
                                <div class="step-circle">
                                    <span class="step-number">2</span>
                                    <i class="fas fa-check d-none"></i>
                                </div>
                                <div class="step-label">Store Details</div>
                            </div>
                            <div class="step-item" data-step="3">
                                <div class="step-circle">
                                    <span class="step-number">3</span>
                                    <i class="fas fa-check d-none"></i>
                                </div>
                                <div class="step-label">Business Address</div>
                            </div>
                            <div class="step-item" data-step="4">
                                <div class="step-circle">
                                    <span class="step-number">4</span>
                                    <i class="fas fa-check d-none"></i>
                                </div>
                                <div class="step-label">Bank Details</div>
                            </div>
                        </div>
                        
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Whoops!</strong> Please fix the following errors.
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('vendor.register') }}" id="registrationForm">
                            @csrf
                            
                            <!-- Step 1: Personal Information -->
                            <div class="form-step active" data-step="1">
                                <h5 class="fw-semibold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-user me-2 text-primary"></i>Personal Information
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="name" class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                                        <input 
                                            id="name" 
                                            type="text" 
                                            class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                            name="name" 
                                            value="{{ old('name') }}" 
                                            required 
                                            autofocus 
                                            placeholder="Enter your full name">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <label for="email" class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                                        <input 
                                            id="email" 
                                            type="email" 
                                            class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                            name="email" 
                                            value="{{ old('email') }}" 
                                            required 
                                            placeholder="Enter your email">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="mobile_number" class="form-label fw-medium">Phone Number <span class="text-danger">*</span></label>
                                        <input 
                                            id="mobile_number" 
                                            type="tel" 
                                            class="form-control form-control-lg @error('mobile_number') is-invalid @enderror" 
                                            name="mobile_number" 
                                            value="{{ old('mobile_number') }}" 
                                            required 
                                            placeholder="Enter your phone number">
                                        @error('mobile_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <label for="password" class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input 
                                                id="password" 
                                                type="password" 
                                                class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                                name="password" 
                                                required 
                                                placeholder="Create a password">
                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="password_confirmation" class="form-label fw-medium">Confirm Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input 
                                                id="password_confirmation" 
                                                type="password" 
                                                class="form-control form-control-lg" 
                                                name="password_confirmation" 
                                                required 
                                                placeholder="Confirm your password">
                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Step 2: Store Information -->
                            <div class="form-step" data-step="2">
                                <h5 class="fw-semibold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-store me-2 text-primary"></i>Store Information
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="store_name" class="form-label fw-medium">Store Name <span class="text-danger">*</span></label>
                                        <input 
                                            id="store_name" 
                                            type="text" 
                                            class="form-control form-control-lg @error('store_name') is-invalid @enderror" 
                                            name="store_name" 
                                            value="{{ old('store_name') }}" 
                                            required 
                                            placeholder="Enter your store name">
                                        @error('store_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <label for="store_slug" class="form-label fw-medium">Store URL Slug</label>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ url('/') }}/store/</span>
                                            <input 
                                                id="store_slug" 
                                                type="text" 
                                                class="form-control form-control-lg @error('store_slug') is-invalid @enderror" 
                                                name="store_slug" 
                                                value="{{ old('store_slug') }}" 
                                                placeholder="your-store">
                                        </div>
                                        <small class="text-muted">Leave empty to auto-generate from store name</small>
                                        @error('store_slug')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="store_description" class="form-label fw-medium">Store Description</label>
                                    <textarea 
                                        id="store_description" 
                                        class="form-control @error('store_description') is-invalid @enderror" 
                                        name="store_description" 
                                        rows="3" 
                                        placeholder="Describe your store and what you sell">{{ old('store_description') }}</textarea>
                                    @error('store_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Step 3: Business Address -->
                            <div class="form-step" data-step="3">
                                <h5 class="fw-semibold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Business Address
                                </h5>
                                
                                <div class="mb-4">
                                    <label for="business_address" class="form-label fw-medium">Street Address <span class="text-danger">*</span></label>
                                    <input 
                                        id="business_address" 
                                        type="text" 
                                        class="form-control form-control-lg @error('business_address') is-invalid @enderror" 
                                        name="business_address" 
                                        value="{{ old('business_address') }}" 
                                        required 
                                        placeholder="Enter your business address">
                                    @error('business_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label for="city" class="form-label fw-medium">City <span class="text-danger">*</span></label>
                                        <input 
                                            id="city" 
                                            type="text" 
                                            class="form-control form-control-lg @error('city') is-invalid @enderror" 
                                            name="city" 
                                            value="{{ old('city') }}" 
                                            required 
                                            placeholder="City">
                                        @error('city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-4 mb-4">
                                        <label for="state" class="form-label fw-medium">State <span class="text-danger">*</span></label>
                                        <input 
                                            id="state" 
                                            type="text" 
                                            class="form-control form-control-lg @error('state') is-invalid @enderror" 
                                            name="state" 
                                            value="{{ old('state') }}" 
                                            required 
                                            placeholder="State">
                                        @error('state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-4 mb-4">
                                        <label for="postal_code" class="form-label fw-medium">Postal Code <span class="text-danger">*</span></label>
                                        <input 
                                            id="postal_code" 
                                            type="text" 
                                            class="form-control form-control-lg @error('postal_code') is-invalid @enderror" 
                                            name="postal_code" 
                                            value="{{ old('postal_code') }}" 
                                            required 
                                            placeholder="Postal Code">
                                        @error('postal_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="country" class="form-label fw-medium">Country <span class="text-danger">*</span></label>
                                    <input 
                                        id="country" 
                                        type="text" 
                                        class="form-control form-control-lg @error('country') is-invalid @enderror" 
                                        name="country" 
                                        value="{{ old('country', 'India') }}" 
                                        required 
                                        placeholder="Country">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Step 4: Bank Account Details -->
                            <div class="form-step" data-step="4">
                                <h5 class="fw-semibold mb-3 pb-2 border-bottom">
                                    <i class="fas fa-university me-2 text-primary"></i>Bank Account Details
                                </h5>
                                <p class="text-muted small mb-3">Your bank details are required for receiving payouts. Please ensure accuracy.</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="account_holder_name" class="form-label fw-medium">Account Holder Name <span class="text-danger">*</span></label>
                                        <input 
                                            id="account_holder_name" 
                                            type="text" 
                                            class="form-control form-control-lg @error('account_holder_name') is-invalid @enderror" 
                                            name="account_holder_name" 
                                            value="{{ old('account_holder_name') }}" 
                                            required 
                                            placeholder="Name as per bank account">
                                        @error('account_holder_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <label for="account_type" class="form-label fw-medium">Account Type</label>
                                        <select 
                                            id="account_type" 
                                            class="form-select form-select-lg @error('account_type') is-invalid @enderror" 
                                            name="account_type">
                                            <option value="savings" {{ old('account_type', 'savings') == 'savings' ? 'selected' : '' }}>Savings Account</option>
                                            <option value="current" {{ old('account_type') == 'current' ? 'selected' : '' }}>Current Account</option>
                                        </select>
                                        @error('account_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="account_number" class="form-label fw-medium">Account Number <span class="text-danger">*</span></label>
                                        <input 
                                            id="account_number" 
                                            type="text" 
                                            class="form-control form-control-lg @error('account_number') is-invalid @enderror" 
                                            name="account_number" 
                                            value="{{ old('account_number') }}" 
                                            required 
                                            minlength="10"
                                            maxlength="20"
                                            placeholder="Enter account number">
                                        <small class="text-muted">10-20 digits</small>
                                        @error('account_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <label for="confirm_account_number" class="form-label fw-medium">Confirm Account Number <span class="text-danger">*</span></label>
                                        <input 
                                            id="confirm_account_number" 
                                            type="text" 
                                            class="form-control form-control-lg @error('confirm_account_number') is-invalid @enderror" 
                                            name="confirm_account_number" 
                                            value="{{ old('confirm_account_number') }}" 
                                            required 
                                            minlength="10"
                                            maxlength="20"
                                            placeholder="Re-enter account number">
                                        @error('confirm_account_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="ifsc_code" class="form-label fw-medium">IFSC Code <span class="text-danger">*</span></label>
                                        <input 
                                            id="ifsc_code" 
                                            type="text" 
                                            class="form-control form-control-lg @error('ifsc_code') is-invalid @enderror" 
                                            name="ifsc_code" 
                                            value="{{ old('ifsc_code') }}" 
                                            required 
                                            maxlength="11"
                                            placeholder="e.g., SBIN0001234"
                                            style="text-transform: uppercase;">
                                        <small class="text-muted">11 characters (e.g., ABCD0123456)</small>
                                        @error('ifsc_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <label for="bank_name" class="form-label fw-medium">Bank Name</label>
                                        <input 
                                            id="bank_name" 
                                            type="text" 
                                            class="form-control form-control-lg @error('bank_name') is-invalid @enderror" 
                                            name="bank_name" 
                                            value="{{ old('bank_name') }}" 
                                            placeholder="e.g., State Bank of India">
                                        @error('bank_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="branch_name" class="form-label fw-medium">Branch Name</label>
                                    <input 
                                        id="branch_name" 
                                        type="text" 
                                        class="form-control form-control-lg @error('branch_name') is-invalid @enderror" 
                                        name="branch_name" 
                                        value="{{ old('branch_name') }}" 
                                        placeholder="e.g., Main Branch, Mumbai">
                                    @error('branch_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" required {{ old('terms') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Vendor Agreement</a>
                                        </label>
                                        @error('terms')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Navigation Buttons -->
                            <div class="step-navigation">
                                <button type="button" class="btn btn-prev btn-lg rounded-pill px-4" id="prevBtn" style="display: none;">
                                    <i class="fas fa-arrow-left me-2"></i>Previous
                                </button>
                                <div></div>
                                <button type="button" class="btn btn-theme btn-lg rounded-pill px-4" id="nextBtn">
                                    Next<i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                <button type="submit" class="btn btn-theme btn-lg rounded-pill px-4" id="submitBtn" style="display: none;">
                                    <i class="fas fa-user-plus me-2"></i>Register as Vendor
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-secondary mb-0">
                                Already have a vendor account? 
                                <a href="{{ route('vendor.login') }}" class="text-primary text-decoration-none fw-medium">Sign in here</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-secondary mb-0">
                        &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentStep = 1;
            const totalSteps = 4;
            
            // Password visibility toggle functionality
            const togglePasswordButtons = document.querySelectorAll('.toggle-password');
            togglePasswordButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
            
            // Auto-generate slug from store name
            const storeNameInput = document.getElementById('store_name');
            const storeSlugInput = document.getElementById('store_slug');
            
            storeNameInput.addEventListener('input', function() {
                if (!storeSlugInput.value || storeSlugInput.dataset.autoGenerated === 'true') {
                    const slug = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/(^-|-$)/g, '');
                    storeSlugInput.value = slug;
                    storeSlugInput.dataset.autoGenerated = 'true';
                }
            });
            
            storeSlugInput.addEventListener('input', function() {
                this.dataset.autoGenerated = 'false';
            });
            
            // Step Navigation
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            const progressBar = document.getElementById('progressBar');
            
            function showStep(step) {
                // Hide all steps
                document.querySelectorAll('.form-step').forEach(function(stepEl) {
                    stepEl.classList.remove('active');
                });
                
                // Show current step
                document.querySelector(`.form-step[data-step="${step}"]`).classList.add('active');
                
                // Update step wizard
                document.querySelectorAll('.step-item').forEach(function(item, index) {
                    const stepNum = index + 1;
                    if (stepNum < step) {
                        item.classList.add('completed');
                        item.classList.remove('active');
                        item.querySelector('.step-number').classList.add('d-none');
                        item.querySelector('i').classList.remove('d-none');
                    } else if (stepNum === step) {
                        item.classList.add('active');
                        item.classList.remove('completed');
                        item.querySelector('.step-number').classList.remove('d-none');
                        item.querySelector('i').classList.add('d-none');
                    } else {
                        item.classList.remove('active', 'completed');
                        item.querySelector('.step-number').classList.remove('d-none');
                        item.querySelector('i').classList.add('d-none');
                    }
                });
                
                // Update progress bar
                const progress = (step / totalSteps) * 100;
                progressBar.style.width = progress + '%';
                
                // Update buttons
                if (step === 1) {
                    prevBtn.style.display = 'none';
                } else {
                    prevBtn.style.display = 'inline-block';
                }
                
                if (step === totalSteps) {
                    nextBtn.style.display = 'none';
                    submitBtn.style.display = 'inline-block';
                } else {
                    nextBtn.style.display = 'inline-block';
                    submitBtn.style.display = 'none';
                }
            }
            
            function validateStep(step) {
                const currentStepEl = document.querySelector(`.form-step[data-step="${step}"]`);
                const requiredFields = currentStepEl.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(function(field) {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                        
                        // Add invalid feedback if not exists
                        if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
                            const feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.textContent = 'This field is required.';
                            
                            if (field.parentElement.classList.contains('input-group')) {
                                field.parentElement.insertAdjacentElement('afterend', feedback);
                            } else {
                                field.insertAdjacentElement('afterend', feedback);
                            }
                        }
                    } else {
                        field.classList.remove('is-invalid');
                    }
                    
                    // Email validation
                    if (field.type === 'email' && field.value.trim()) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(field.value)) {
                            field.classList.add('is-invalid');
                            isValid = false;
                        }
                    }
                    
                    // Password confirmation validation
                    if (field.id === 'password_confirmation' && step === 1) {
                        const password = document.getElementById('password').value;
                        if (field.value !== password) {
                            field.classList.add('is-invalid');
                            isValid = false;
                            
                            let feedback = field.parentElement.nextElementSibling;
                            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                                feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback d-block';
                                field.parentElement.insertAdjacentElement('afterend', feedback);
                            }
                            feedback.textContent = 'Passwords do not match.';
                        }
                    }
                });
                
                return isValid;
            }
            
            nextBtn.addEventListener('click', function() {
                if (validateStep(currentStep)) {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        showStep(currentStep);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                } else {
                    // Scroll to first invalid field
                    const firstInvalid = document.querySelector('.form-step.active .is-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalid.focus();
                    }
                }
            });
            
            prevBtn.addEventListener('click', function() {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
            
            // Form submission validation
            document.getElementById('registrationForm').addEventListener('submit', function(e) {
                if (!validateStep(currentStep)) {
                    e.preventDefault();
                    const firstInvalid = document.querySelector('.form-step.active .is-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalid.focus();
                    }
                }
            });
            
            // Remove invalid class on input
            document.querySelectorAll('input, select, textarea').forEach(function(field) {
                field.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('is-invalid');
                    }
                });
            });
            
            // Initialize first step
            showStep(currentStep);
        });
    </script>
</body>
</html>
