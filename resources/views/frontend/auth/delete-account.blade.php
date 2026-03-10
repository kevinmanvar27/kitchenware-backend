@extends('frontend.layouts.auth')

@section('title', 'Delete Account - ' . setting('site_title', 'Frontend App'))

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
                    
                    <div class="text-center mb-4">
                        <div class="delete-icon mb-3">
                            <i class="fas fa-user-times fa-3x text-danger"></i>
                        </div>
                        <h1 class="h2 fw-bold heading-text auth-title">Delete Account</h1>
                        <p class="general-text mb-0 auth-subtitle">Permanently delete your account and all associated data</p>
                    </div>
                    
                    <!-- Warning Alert -->
                    <div class="alert alert-warning mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action is irreversible. All your data including cart items, wishlist, and notifications will be permanently deleted.
                    </div>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2 alert-icon"></i>
                            <strong>Error!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
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
                    
                    <form method="POST" action="{{ route('account.delete.submit') }}" id="delete-account-form">
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
                                placeholder="Enter your registered email">
                            
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
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <div class="mb-4 form-group-animated">
                            <label for="reason" class="form-label fw-medium label-text">
                                <i class="fas fa-comment me-2 label-icon"></i>Reason for Deletion (Optional)
                            </label>
                            <textarea 
                                id="reason" 
                                class="form-control @error('reason') is-invalid @enderror" 
                                name="reason" 
                                rows="3"
                                placeholder="Please let us know why you're leaving (optional)">{{ old('reason') }}</textarea>
                            
                            @error('reason')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <div class="mb-4 form-group-animated">
                            <div class="form-check">
                                <input 
                                    class="form-check-input @error('confirm_deletion') is-invalid @enderror" 
                                    type="checkbox" 
                                    name="confirm_deletion" 
                                    id="confirm_deletion" 
                                    value="1"
                                    {{ old('confirm_deletion') ? 'checked' : '' }}>
                                <label class="form-check-label label-text" for="confirm_deletion">
                                    <strong>I understand that this action is permanent and cannot be undone.</strong>
                                </label>
                                @error('confirm_deletion')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-grid form-group-animated">
                            <button type="submit" class="btn btn-danger btn-lg rounded-pill px-4" id="delete-btn">
                                <span><i class="fas fa-trash-alt me-2 btn-icon"></i>Delete My Account</span>
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
            
            <div class="text-center mt-3 auth-footer">
                <p class="general-text mb-0">
                    Changed your mind? <a href="{{ route('login') }}" class="login-link">Go back to Login <i class="fas fa-arrow-right ms-1"></i></a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .delete-icon {
        color: #dc3545;
    }
    
    .btn-danger {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
        color: white !important;
    }
    
    .btn-danger:hover {
        background-color: #bb2d3b !important;
        border-color: #b02a37 !important;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
    }
    
    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffecb5;
        color: #664d03;
    }
    
    .login-link {
        color: var(--theme-color) !important;
        text-decoration: none;
        font-weight: 500;
    }
    
    .login-link:hover {
        color: var(--link-hover-color) !important;
        text-decoration: underline;
    }
</style>
@endsection

@section('scripts')
<script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(function(button) {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Confirmation before form submission
    document.getElementById('delete-account-form').addEventListener('submit', function(e) {
        const confirmed = confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.');
        if (!confirmed) {
            e.preventDefault();
            return false;
        }
        
        // Add loading state to button
        const btn = document.getElementById('delete-btn');
        btn.classList.add('btn-loading');
        btn.disabled = true;
    });
</script>
@endsection
