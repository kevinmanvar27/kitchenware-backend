@extends('frontend.layouts.auth')

@section('title', 'Register - ' . setting('site_title', 'Frontend App'))

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
                        <h1 class="h2 fw-bold heading-text auth-title">Create Account</h1>
                        <p class="general-text mb-0 auth-subtitle">Fill in the details below to get started</p>
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
                    
                    <form method="POST" action="{{ route('frontend.register') }}" id="register-form">
                        @csrf
                        
                        <div class="mb-4 form-group-animated">
                            <label for="name" class="form-label fw-medium label-text">
                                <i class="fas fa-user me-2 label-icon"></i>Full Name
                            </label>
                            <input 
                                id="name" 
                                type="text" 
                                class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                name="name" 
                                value="{{ old('name') }}" 
                                required 
                                autocomplete="name" 
                                autofocus 
                                placeholder="Enter your full name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
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
                                placeholder="Enter your email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
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
                                    autocomplete="new-password" 
                                    placeholder="Enter your password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="password-strength mt-2" id="password-strength"></div>
                        </div>
                        
                        <div class="mb-4 form-group-animated">
                            <label for="password-confirm" class="form-label fw-medium label-text">
                                <i class="fas fa-lock me-2 label-icon"></i>Confirm Password
                            </label>
                            <div class="input-group">
                                <input 
                                    id="password-confirm" 
                                    type="password" 
                                    class="form-control form-control-lg" 
                                    name="password_confirmation" 
                                    required 
                                    autocomplete="new-password" 
                                    placeholder="Confirm your password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password-confirm">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-match mt-2" id="password-match"></div>
                        </div>
                        
                        <div class="d-grid form-group-animated">
                            <button type="submit" class="btn btn-theme btn-lg rounded-pill px-4" id="register-btn">
                                <span><i class="fas fa-user-plus me-2 btn-icon"></i>Create Account</span>
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4 auth-footer">
                        <p class="general-text mb-0">
                            Already have an account? <a href="{{ route('login') }}" class="login-link">Sign In <i class="fas fa-arrow-right ms-1"></i></a>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4 auth-footer">
                <p class="general-text mb-0">
                    &copy; {{ date('Y') }} {{ setting('site_title', 'Frontend App') }}. All rights reserved.
                </p>
            </div>
            
            <!-- Documentation Guide Link -->
            <div class="doc-guide-banner mt-4">
                <div class="doc-guide-content">
                    <div class="doc-guide-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="doc-guide-text">
                        <span class="doc-guide-title">Learn About Distributor App</span>
                        <span class="doc-guide-subtitle">Discover powerful features designed for your business success</span>
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
    
    /* Login link */
    .login-link {
        position: relative;
        font-weight: 600;
    }
    
    .login-link i {
    }
    
    .login-link:hover i {
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
    
    /* Password strength indicator */
    .password-strength {
        height: 4px;
        border-radius: 2px;
        overflow: hidden;
    }
    
    .password-strength .strength-bar {
        height: 100%;
        border-radius: 2px;
    }
    
    .strength-weak { background: linear-gradient(90deg, #dc3545, #dc3545); width: 33%; }
    .strength-medium { background: linear-gradient(90deg, #ffc107, #ffc107); width: 66%; }
    .strength-strong { background: linear-gradient(90deg, #28a745, #28a745); width: 100%; }
    
    .strength-text {
        font-size: 0.75rem;
        margin-top: 4px;
    }
    
    .strength-text.weak { color: #dc3545; }
    .strength-text.medium { color: #ffc107; }
    .strength-text.strong { color: #28a745; }
    
    /* Password match indicator */
    .password-match {
        font-size: 0.75rem;
    }
    
    .password-match.match { color: #28a745; }
    .password-match.no-match { color: #dc3545; }
    
    .password-match i {
        margin-right: 4px;
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
        
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthContainer = document.getElementById('password-strength');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let strengthClass = '';
            let strengthText = '';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            if (password.length === 0) {
                strengthContainer.innerHTML = '';
            } else if (strength <= 1) {
                strengthClass = 'weak';
                strengthText = 'Weak password';
            } else if (strength <= 2) {
                strengthClass = 'medium';
                strengthText = 'Medium password';
            } else {
                strengthClass = 'strong';
                strengthText = 'Strong password';
            }
            
            if (password.length > 0) {
                strengthContainer.innerHTML = `
                    <div class="strength-bar strength-${strengthClass}"></div>
                    <div class="strength-text ${strengthClass}"><i class="fas fa-${strengthClass === 'strong' ? 'check-circle' : strengthClass === 'medium' ? 'exclamation-circle' : 'times-circle'}"></i>${strengthText}</div>
                `;
            }
        });
        
        // Password match indicator
        const confirmInput = document.getElementById('password-confirm');
        const matchContainer = document.getElementById('password-match');
        
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            
            if (confirm.length === 0) {
                matchContainer.innerHTML = '';
            } else if (password === confirm) {
                matchContainer.innerHTML = '<span class="match"><i class="fas fa-check-circle"></i>Passwords match</span>';
                matchContainer.className = 'password-match match';
            } else {
                matchContainer.innerHTML = '<span class="no-match"><i class="fas fa-times-circle"></i>Passwords do not match</span>';
                matchContainer.className = 'password-match no-match';
            }
        }
        
        confirmInput.addEventListener('input', checkPasswordMatch);
        passwordInput.addEventListener('input', checkPasswordMatch);
        
        // Form submission with loading state
        const registerForm = document.getElementById('register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                // Add loading state to button
                const submitBtn = document.getElementById('register-btn');
                submitBtn.classList.add('btn-loading');
            });
        }
        
        // Input focus animation - add glow effect to parent
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                const parent = this.closest('.mb-4');
                if (parent) parent.classList.add('input-focused');
            });
            input.addEventListener('blur', function() {
                const parent = this.closest('.mb-4');
                if (parent) parent.classList.remove('input-focused');
            });
        });
    });
</script>
@endsection
