@extends('frontend.layouts.auth')

@section('title', 'Login - ' . setting('site_title', 'Frontend App'))

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 auth-card">
                <div class="card-body p-5">
                    <!-- Logo Section -->
                    @if(setting('logo'))
                    <div class="text-center mb-4 auth-logo">
                        <img src="{{ asset('storage/' . setting('logo')) }}" alt="{{ setting('site_title', 'Logo') }}" style="max-height: 60px;">
                    </div>
                    @endif
                    
                    <div class="text-center mb-5">
                        <h1 class="h2 fw-bold heading-text auth-title">Welcome Back</h1>
                        <p class="general-text mb-0 auth-subtitle">Please sign in to your account</p>
                    </div>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2 alert-icon"></i>
                            <strong>Whoops!</strong> Something went wrong.
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2 alert-icon"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2 alert-icon"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('login') }}" id="login-form">
                        @csrf
                        
                        <div class="mb-4 form-group-animated">
                            <label for="email" class="form-label fw-medium label-text">
                                <i class="fas fa-envelope me-2 label-icon"></i>Email Address
                            </label>
                            <input 
                                id="email" 
                                type="email" 
                                class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autocomplete="email" 
                                autofocus 
                                placeholder="Enter your email">
                            
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <div class="mb-4 form-group-animated">
                            <label for="password" class="form-label fw-medium label-text">
                                <i class="fas fa-lock me-2 label-icon"></i>Password
                            </label>
                            <div class="input-group">
                                <input 
                                    id="password" 
                                    type="password" 
                                    class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                    name="password" 
                                    required 
                                    autocomplete="current-password" 
                                    placeholder="Enter your password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <!-- Hidden input to store guest cart data -->
                        <input type="hidden" name="guest_cart" id="guest_cart" value="[]">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4 form-group-animated">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label label-text" for="remember">
                                    Remember Me
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid form-group-animated">
                            <button type="submit" class="btn btn-theme btn-lg rounded-pill px-4" id="login-btn">
                                <span><i class="fas fa-sign-in-alt me-2 btn-icon"></i>Sign In</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4 auth-footer">
                <p class="general-text mb-0">
                    &copy; {{ date('Y') }} {{ setting('site_title', 'Frontend App') }}. All rights reserved.
                </p>
            </div>
            
            <!-- Link to Unified Login -->
            <div class="text-center mt-3 auth-footer">
                <p class="general-text mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Are you a Vendor or Admin? <a href="{{ route('login') }}" class="register-link">Use Unified Login <i class="fas fa-arrow-right ms-1"></i></a>
                </p>
            </div>
                                
            @if(!isset($accessPermission) || $accessPermission !== 'registered_users_only')
            <div class="text-center mt-3 auth-footer">
                <p class="general-text mb-0">
                    Don't have an account? <a href="{{ route('frontend.register') }}" class="register-link">Register <i class="fas fa-arrow-right ms-1"></i></a>
                </p>
            </div>
            @endif
            
            <!-- Documentation Guide Link -->
            <div class="doc-guide-banner mt-4">
                <div class="doc-guide-content">
                    <div class="doc-guide-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="doc-guide-text">
                        <span class="doc-guide-title">New to Distributor App?</span>
                        <span class="doc-guide-subtitle">Explore our comprehensive guide to understand all features</span>
                    </div>
                    <a href="{{ route('client.documentation') }}" class="doc-guide-btn" target="_blank">
                        <span>View Guide</span>
                        <i class="fas fa-external-link-alt ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Alert icon */
    .alert-icon {
    }
    
    .alert-success .alert-icon {
    }
    
    /* Label icon */
    .label-icon {
    }
    
    .form-control:focus ~ .label-icon,
    .input-group:focus-within .label-icon {
        color: var(--theme-color);
    }
    
    /* Button icon */
    .btn-icon {
    }
    
    .btn-theme:hover .btn-icon {
    }
    
    /* Register link */
    .register-link {
        position: relative;
        font-weight: 600;
    }
    
    .register-link i {
    }
    
    .register-link:hover i {
    }
    
    /* Form validation */
    .is-invalid {
    }
    
    /* Card hover effect */
    .auth-card {
    }
    
    .auth-card:hover {
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Documentation Guide Banner */
    .doc-guide-banner {
        background: linear-gradient(135deg, #FF6B00 0%, #ff8533 100%);
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 10px 40px -10px rgba(255, 107, 0, 0.4);
        position: relative;
        overflow: hidden;
    }
    
    .doc-guide-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        pointer-events: none;
    }
    
    .doc-guide-content {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }
    
    .doc-guide-icon {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .doc-guide-icon i {
        font-size: 1.5rem;
        color: #fff;
    }
    
    .doc-guide-text {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .doc-guide-title {
        font-weight: 700;
        font-size: 1rem;
        color: #fff;
        line-height: 1.3;
    }
    
    .doc-guide-subtitle {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.4;
    }
    
    .doc-guide-btn {
        display: inline-flex;
        align-items: center;
        background: #fff;
        color: #FF6B00;
        padding: 10px 20px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.3s ease;
        flex-shrink: 0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .doc-guide-btn:hover {
        background: #333333;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }
    
    .doc-guide-btn i {
        font-size: 0.8rem;
    }
    
    /* Responsive adjustments for doc guide banner */
    @media (max-width: 576px) {
        .doc-guide-content {
            flex-direction: column;
            text-align: center;
        }
        
        .doc-guide-text {
            align-items: center;
        }
        
        .doc-guide-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        
        // Add guest cart data to form before submission
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                // Get guest cart from localStorage
                const guestCart = localStorage.getItem('guest_cart') || '[]';
                document.getElementById('guest_cart').value = guestCart;
                
                // Add loading state to button
                const submitBtn = document.getElementById('login-btn');
                submitBtn.classList.add('btn-loading');
            });
        }
        
        // Input focus animation - add glow effect to parent
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.mb-4').classList.add('input-focused');
            });
            input.addEventListener('blur', function() {
                this.closest('.mb-4').classList.remove('input-focused');
            });
        });
    });
    
    // Clear localStorage cart after successful login
    window.addEventListener('load', function() {
        // Check if we just came from a successful login
        if (window.location.search.includes('login=success') || 
            document.querySelector('.alert-success') || 
            document.querySelector('.alert-info')) {
            // Clear guest cart from localStorage
            localStorage.removeItem('guest_cart');
        }
    });
</script>
@endsection
