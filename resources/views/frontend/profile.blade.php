@extends('frontend.layouts.app')

@section('title', 'Profile - ' . setting('site_title', 'Frontend App'))

@section('content')
<div class="container py-5">
    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show alert-animated" role="alert">
            <i class="fas fa-check-circle me-2 success-icon"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <!-- Error Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show alert-animated" role="alert">
            <i class="fas fa-exclamation-triangle me-2 error-icon"></i>
            <strong>Whoops!</strong> There were some problems with your input.
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="row">
        <!-- Profile Sidebar -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 profile-card">
                <div class="card-header bg-white border-0">
                    <h3 class="h5 mb-0 fw-bold heading-text">Profile</h3>
                </div>
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block avatar-container mb-3">
                        @if($user->avatar)
                            <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->name }}" class="img-fluid rounded-circle avatar-image" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="bg-theme rounded-circle d-flex align-items-center justify-content-center mx-auto avatar-placeholder" style="width: 150px; height: 150px; background-color: {{ setting('theme_color', '#007bff') }};">
                                <i class="fas fa-user text-white avatar-icon" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                        
                        <!-- Avatar Upload Form -->
                        <form id="avatar-form" action="{{ route('frontend.profile.avatar.update') }}" method="POST" enctype="multipart/form-data" class="d-none">
                            @csrf
                            <input type="file" name="avatar" id="avatar-input" accept="image/*">
                        </form>
                        
                        <button type="button" class="btn btn-sm btn-theme rounded-circle position-absolute camera-btn" id="change-avatar-btn" style="width: 40px; height: 40px; bottom: 5px; right: 5px;">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    
                    <h3 class="h5 fw-bold mb-1 user-name">{{ $user->name }}</h3>
                    <p class="text-muted mb-3 user-email">{{ $user->email }}</p>
                    
                    <!-- Remove Avatar Form -->
                    @if($user->avatar)
                    <form action="{{ route('frontend.profile.avatar.remove') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill remove-photo-btn" onclick="return confirm('Are you sure you want to remove your profile picture?')">
                            <i class="fas fa-trash me-1"></i>Remove Photo
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            
            <div class="card shadow-sm border-0 mt-4 nav-card">
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#profile-details" class="list-group-item list-group-item-action active nav-link-item">
                            <i class="fas fa-user me-2 nav-icon"></i>Profile Details
                        </a>
                        <a href="#change-password" class="list-group-item list-group-item-action nav-link-item">
                            <i class="fas fa-key me-2 nav-icon"></i>Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Content -->
        <div class="col-lg-8">
            <!-- Profile Details Section -->
            <div class="card shadow-sm border-0 mb-4 form-card" id="profile-details">
                <div class="card-header bg-white border-0">
                    <h3 class="h5 mb-0 fw-bold heading-text section-title">
                        <i class="fas fa-user-edit me-2 section-icon"></i>Profile Details
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('frontend.profile.update') }}" method="POST" class="profile-form">
                        @csrf
                        @method('POST')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-medium label-text">Full Name <span class="text-danger">*</span></label>
                                <div class="input-wrapper">
                                    <input type="text" class="form-control form-input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    <span class="input-focus-border"></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-medium label-text">Email Address <span class="text-danger">*</span></label>
                                <div class="input-wrapper">
                                    <input type="email" class="form-control form-input" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    <span class="input-focus-border"></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="mobile_number" class="form-label fw-medium label-text">Mobile Number</label>
                                <div class="input-wrapper">
                                    <input type="text" class="form-control form-input" id="mobile_number" name="mobile_number" value="{{ old('mobile_number', $user->mobile_number) }}">
                                    <span class="input-focus-border"></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label fw-medium label-text">Date of Birth</label>
                                <div class="input-wrapper">
                                    <input type="date" class="form-control form-input" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
                                    <span class="input-focus-border"></span>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="address" class="form-label fw-medium label-text">Address</label>
                                <div class="input-wrapper">
                                    <textarea class="form-control form-input" id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                                    <span class="input-focus-border"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-theme rounded-pill px-4 submit-btn">
                                <i class="fas fa-save me-2 btn-icon"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password Section -->
            <div class="card shadow-sm border-0 form-card" id="change-password">
                <div class="card-header bg-white border-0">
                    <h3 class="h5 mb-0 fw-bold heading-text section-title">
                        <i class="fas fa-lock me-2 section-icon"></i>Change Password
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('frontend.profile.password.change') }}" method="POST" class="password-form">
                        @csrf
                        @method('POST')
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="current_password" class="form-label fw-medium label-text">Current Password <span class="text-danger">*</span></label>
                                <div class="input-group password-input-group">
                                    <input type="password" class="form-control form-input" id="current_password" name="current_password" required>
                                    <button class="btn btn-outline-theme toggle-password" type="button" data-target="current_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-medium label-text">New Password <span class="text-danger">*</span></label>
                                <div class="input-group password-input-group">
                                    <input type="password" class="form-control form-input" id="password" name="password" required>
                                    <button class="btn btn-outline-theme toggle-password" type="button" data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text password-hint">Password must be at least 8 characters long.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label fw-medium label-text">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="input-group password-input-group">
                                    <input type="password" class="form-control form-input" id="password_confirmation" name="password_confirmation" required>
                                    <button class="btn btn-outline-theme toggle-password" type="button" data-target="password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-theme rounded-pill px-4 submit-btn">
                                <i class="fas fa-key me-2 btn-icon"></i>Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --theme-color: {{ setting('theme_color', '#007bff') }};
        --hover-color: {{ setting('link_hover_color', '#0056b3') }};
    }
    
    /* Alert styles */
    .alert-animated { }
    .success-icon { color: #28a745; }
    .error-icon { }
    
    /* Profile Card */
    .profile-card { overflow: hidden; }
    .profile-card:hover { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important; }
    
    /* Avatar */
    .avatar-container { position: relative; }
    .avatar-image, .avatar-placeholder { border: 4px solid transparent; }
    .avatar-container:hover .avatar-image,
    .avatar-container:hover .avatar-placeholder { border-color: var(--theme-color); box-shadow: 0 0 20px rgba(0, 123, 255, 0.4); }
    .avatar-icon { }
    .avatar-container:hover .avatar-icon { }
    
    /* Camera button */
    .camera-btn { opacity: 0.8; }
    .camera-btn:hover { opacity: 1; }
    .avatar-container:hover .camera-btn { }
    
    /* User info */
    .user-name { }
    .user-email { }
    
    /* Remove photo button */
    .remove-photo-btn { }
    .remove-photo-btn:hover { box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); }
    .remove-photo-btn:hover i { }
    
    /* Navigation Card */
    .nav-card { }
    .nav-card:hover { box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12) !important; }
    
    /* Navigation items */
    .nav-link-item { position: relative; overflow: hidden; border: none !important; }
    .nav-link-item::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 3px; background-color: var(--theme-color); }
    .nav-link-item:hover::before, .nav-link-item.active::before { }
    .nav-link-item:hover { padding-left: 25px; background-color: rgba(0, 123, 255, 0.05); }
    .nav-link-item.active { background-color: var(--theme-color) !important; border-color: var(--theme-color) !important; color: white !important; }
    .nav-icon { }
    .nav-link-item:hover .nav-icon { }
    
    /* Form Cards */
    .form-card { border-left: 4px solid transparent; }
    .form-card:hover { border-left-color: var(--theme-color); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important; }
    
    /* Section titles */
    .section-title { color: var(--theme-color); }
    .section-icon { }
    .section-title:hover .section-icon { }
    
    /* Form inputs */
    .input-wrapper { position: relative; }
    .form-input { border: 2px solid #e0e0e0; }
    .form-input:hover { border-color: #b0b0b0; }
    .form-input:focus { border-color: var(--theme-color); box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1); }
    .input-focus-border { position: absolute; bottom: 0; left: 0; width: 100%; height: 2px; background-color: var(--theme-color); }
    .form-input:focus ~ .input-focus-border { }
    
    /* Labels */
    .label-text { }
    .form-input:focus ~ .label-text,
    .input-wrapper:focus-within + .label-text { color: var(--theme-color); }
    
    /* Password input group */
    .password-input-group { }
    .password-input-group:focus-within { box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1); border-radius: 0.375rem; }
    .password-input-group .form-input:focus { box-shadow: none; }
    .password-hint { font-size: 0.85rem; color: #6c757d; margin-top: 0.5rem; }
    
    /* Toggle password button */
    .toggle-password { }
    .toggle-password:hover { background-color: var(--theme-color); color: white; }
    .toggle-password:focus { box-shadow: none !important; }
    .toggle-password i { }
    .toggle-password:hover i { }
    
    /* Submit buttons */
    .submit-btn { position: relative; overflow: hidden; }
    .submit-btn::before { content: ''; position: absolute; top: 50%; left: 50%; width: 0; height: 0; background: rgba(255, 255, 255, 0.2); border-radius: 50%; }
    .submit-btn:hover::before { }
    .submit-btn:hover { box-shadow: 0 8px 20px rgba(0, 123, 255, 0.3); }
    .submit-btn:active { }
    .btn-icon { }
    .submit-btn:hover .btn-icon { }
    
    /* Theme buttons */
    .btn-theme { background-color: var(--theme-color) !important; border-color: var(--theme-color) !important; color: white !important; }
    .btn-theme:hover { background-color: var(--hover-color) !important; border-color: var(--hover-color) !important; }
    
    .btn-outline-theme { border-color: var(--theme-color) !important; color: var(--theme-color) !important; }
    .btn-outline-theme:hover { background-color: var(--theme-color) !important; border-color: var(--theme-color) !important; color: white !important; }
    
    /* Form submission */
    .form-submitting .submit-btn { pointer-events: none; }
    .form-submitting .submit-btn .btn-icon { }
    
    /* Responsive */
    @media (max-width: 768px) {
        .profile-card, .nav-card, .form-card { margin-bottom: 1.5rem; }
        .avatar-image, .avatar-placeholder { width: 120px !important; height: 120px !important; }
        .form-card:hover { transform: none; }
    }
    
    /* Scroll highlight effect */
    .form-card:target { border-left-color: var(--theme-color); }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle avatar change
        const changeAvatarBtn = document.getElementById('change-avatar-btn');
        const avatarInput = document.getElementById('avatar-input');
        const avatarForm = document.getElementById('avatar-form');
        
        if (changeAvatarBtn && avatarInput) {
            changeAvatarBtn.addEventListener('click', function() {
                avatarInput.click();
            });
            
            avatarInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const fileType = file.type;
                    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                    
                    if (!validTypes.includes(fileType)) {
                        showToast('Please select a valid image file (JPEG, PNG, JPG, GIF).', 'error');
                        return;
                    }
                    
                    const fileSize = file.size / 1024 / 1024;
                    if (fileSize > 2) {
                        showToast('File size exceeds 2MB. Please select a smaller file.', 'error');
                        return;
                    }
                    
                    // Show loading animation (use appendChild to avoid destroying form)
                    const avatarContainer = document.querySelector('.avatar-container');
                    avatarContainer.style.opacity = '0.5';
                    const spinnerDiv = document.createElement('div');
                    spinnerDiv.className = 'spinner-overlay';
                    spinnerDiv.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
                    avatarContainer.appendChild(spinnerDiv);
                    
                    avatarForm.submit();
                }
            });
        }
        
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
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    // Update active state in sidebar
                    document.querySelectorAll('.list-group-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Scroll to target element with offset
                    const offset = 100;
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;
                    
                    window.scrollTo({
                        top: offsetPosition
                    });
                }
            });
        });
        
        // Handle section visibility based on URL hash
        function handleSectionVisibility() {
            const hash = window.location.hash;
            if (hash) {
                document.querySelectorAll('.list-group-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                const activeLink = document.querySelector(`a[href="${hash}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
                
                const targetElement = document.querySelector(hash);
                if (targetElement) {
                    setTimeout(() => {
                        const offset = 100;
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - offset;
                        
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }, 300);
                }
            }
        }
        
        handleSectionVisibility();
        window.addEventListener('hashchange', handleSectionVisibility);
        
        // Form submission animation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                this.classList.add('form-submitting');
                const btn = this.querySelector('.submit-btn');
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-spinner me-2 btn-icon"></i>Processing...';
                }
            });
        });
        
        // Input focus animations
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('input-focused');
            });
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('input-focused');
            });
        });
        
        // Toast function
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            toast.addEventListener('hidden.bs.toast', () => document.body.removeChild(toast));
        }
    });
</script>
@endsection
