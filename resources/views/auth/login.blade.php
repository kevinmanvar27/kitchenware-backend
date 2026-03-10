<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
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
            min-height: 100vh;
            font-family: 'Figtree', sans-serif;
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
        
        /* Container positioning */
        .container {
            position: relative;
            z-index: 2;
        }
        
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.4s ease;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .login-card:hover {
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-color) 100%);
            padding: 2rem;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background pattern for header */
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255, 255, 255, 0.05) 10px,
                rgba(255, 255, 255, 0.05) 20px
            );
            animation: slidePattern 20s linear infinite;
        }
        
        @keyframes slidePattern {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
            }
        }
        
        .login-header .logo-container {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
            animation: logoFloat 3s ease-in-out infinite;
        }
        
        @keyframes logoFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        .login-header .logo-container img {
            max-height: 50px;
            max-width: 50px;
            object-fit: contain;
        }
        
        .login-header .logo-container i {
            font-size: 2rem;
        }
        
        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            opacity: 0.9;
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        
        .login-body {
            padding: 2.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-label i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }
        
        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
        }
        
        .form-control::placeholder {
            color: #9ca3af;
        }
        
        .input-group .form-control {
            border-right: none;
        }
        
        .input-group .btn-outline-secondary {
            border: 2px solid #e5e7eb;
            border-left: none;
            border-radius: 0 12px 12px 0;
            background: white;
            color: #6b7280;
            transition: all 0.3s ease;
        }
        
        .input-group .btn-outline-secondary:hover {
            background: #f3f4f6;
            color: var(--primary-color);
        }
        
        .input-group:focus-within .btn-outline-secondary {
            border-color: var(--primary-color);
        }
        
        .btn-theme {
            background: var(--primary-color);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.875rem 2rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .btn-theme:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            color: white;
        }
        
        .btn-theme:active {
            transform: translateY(0);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-label {
            color: #6b7280;
        }
        
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }
        
        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
        }
        
        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
        }
        
        .alert-warning {
            background-color: #fffbeb;
            color: #92400e;
        }
        
        .user-type-info {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .user-type-info h6 {
            color: #374151;
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .user-type-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin: 0.25rem;
            transition: all 0.3s ease;
        }
        
        .user-type-badge i {
            margin-right: 0.5rem;
        }
        
        .badge-admin {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-vendor {
            background: #dcfce7;
            color: #166534;
        }
        
        .badge-staff {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-user {
            background: #f3e8ff;
            color: #7c3aed;
        }
        
        .footer-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
            opacity: 0.8;
        }
        
        .copyright {
            text-align: center;
            color: #9ca3af;
            font-size: 0.875rem;
            margin-top: 2rem;
        }
        
        /* Loading state */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        .btn-loading span {
            visibility: hidden;
        }
        
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-body {
                padding: 1.5rem;
            }
            
            .login-header {
                padding: 1.5rem;
            }
            
            .user-type-badge {
                display: block;
                text-align: center;
                margin: 0.5rem 0;
            }
        }
        
        /* Documentation Guide Banner */
        .doc-guide-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-color) 100%);
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
            color: var(--primary-color);
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
</head>
<body class="d-flex align-items-center py-4">
    <!-- Animated Background Shapes -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        <div class="shape shape-5"></div>
    </div>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-6 col-xl-5">
                <div class="card login-card">
                    <!-- Login Header -->
                    <div class="login-header">
                        <div class="logo-container">
                            @if(setting('header_logo'))
                                <img src="{{ asset('storage/' . setting('header_logo')) }}" alt="{{ setting('site_title', 'App') }}">
                            @else
                                <i class="fas fa-sign-in-alt text-white"></i>
                            @endif
                        </div>
                        <h1 class="text-white">Welcome Back</h1>
                        <p>Sign in to access your account</p>
                    </div>
                    
                    <!-- Login Body -->
                    <div class="login-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Whoops!</strong> Something went wrong.
                                <ul class="mb-0 mt-2 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('login.post') }}" id="login-form">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>Email Address
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
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i>Password
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
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <!-- Hidden input to store guest cart data -->
                            <input type="hidden" name="guest_cart" id="guest_cart" value="[]">
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        Remember Me
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-theme btn-lg" id="login-btn">
                                    <span><i class="fas fa-sign-in-alt me-2"></i>Sign In</span>
                                </button>
                            </div>
                        </form>
                        
                        <!-- User Type Information -->
                        <div class="user-type-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Supported Account Types</h6>
                            <div class="d-flex flex-wrap justify-content-center">
                                <span class="user-type-badge badge-admin">
                                    <i class="fas fa-user-shield"></i>Admin
                                </span>
                                <span class="user-type-badge badge-vendor">
                                    <i class="fas fa-store"></i>Vendor
                                </span>
                                <span class="user-type-badge badge-staff">
                                    <i class="fas fa-user-tie"></i>Staff
                                </span>
                                <span class="user-type-badge badge-user">
                                    <i class="fas fa-user"></i>Customer
                                </span>
                            </div>
                        </div>
                        
                        <!-- Documentation Guide Link -->
                        <div class="doc-guide-banner mt-4">
                            <div class="doc-guide-content">
                                <div class="doc-guide-icon">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="doc-guide-text">
                                    <span class="doc-guide-title">New to {{ config('app.name', 'Distributor App') }}?</span>
                                    <span class="doc-guide-subtitle">Explore our comprehensive guide to understand all features</span>
                                </div>
                                <a href="{{ route('client.documentation') }}" class="doc-guide-btn" target="_blank">
                                    <span>View Guide</span>
                                    <i class="fas fa-external-link-alt ms-2"></i>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Footer Links -->
                        <div class="footer-links">
                            @if(!isset($accessPermission) || $accessPermission !== 'registered_users_only')
                            <p class="mb-2">
                                Don't have an account? <a href="{{ route('frontend.register') }}">Register here <i class="fas fa-arrow-right ms-1"></i></a>
                            </p>
                            @endif
                            <p class="mb-0">
                                Want to become a vendor? <a href="{{ route('vendor.register') }}">Apply now <i class="fas fa-arrow-right ms-1"></i></a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <p class="copyright">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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
        });
        
        // Clear localStorage cart after successful login
        window.addEventListener('load', function() {
            // Check if we just came from a successful login
            if (window.location.search.includes('login=success') || 
                document.querySelector('.alert-success')) {
                // Clear guest cart from localStorage
                localStorage.removeItem('guest_cart');
            }
        });
    </script>
</body>
</html>
