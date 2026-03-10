@extends('admin.layouts.app')

@section('page-title', 'Profile')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'User Profile'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <div class="card border-default shadow-sm">
                            <div class="card-header bg-surface border-default d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-semibold">User Profile</h5>
                                <div class="d-flex">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill me-2" id="change-password-btn">
                                        <i class="fas fa-key me-1"></i> Change Password
                                    </button>
                                    <button type="button" class="btn btn-sm btn-theme rounded-pill" id="refresh-btn">
                                        <i class="fas fa-sync-alt me-1"></i> Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Whoops!</strong> There were some problems with your input.
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="row">
                                    <!-- Profile Picture Section -->
                                    <div class="col-lg-4 mb-4 mb-lg-0">
                                        <div class="card border-default shadow-sm h-100">
                                            <div class="card-header bg-surface border-default">
                                                <h6 class="mb-0 fw-semibold">Profile Picture</h6>
                                            </div>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <img src="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random' }}" 
                                                        alt="{{ $user->name }}" 
                                                        class="rounded-circle border border-default shadow-sm profile-avatar"
                                                        width="128" height="128"
                                                        id="profile-avatar">
                                                </div>
                                                <h5 class="mb-1 fw-semibold">{{ $user->name }}</h5>
                                                <p class="text-secondary mb-3">{{ $user->email }}</p>
                                                <span class="badge bg-primary-subtle text-primary">{{ ucfirst(str_replace('_', ' ', $user->user_role)) }}</span>
                                                <div class="mt-4">
                                                    <form method="POST" action="{{ route('admin.profile.avatar.update') }}" enctype="multipart/form-data" id="avatar-form">
                                                        @csrf
                                                        @method('POST')
                                                        <input type="file" name="avatar" id="avatar-upload" class="d-none" accept="image/*">
                                                        <label for="avatar-upload" class="btn btn-sm btn-outline-secondary rounded-pill w-100">
                                                            <i class="fas fa-upload me-1"></i> Upload New Photo
                                                        </label>
                                                        
                                                        @if($user->avatar)
                                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill w-100 mt-2" id="remove-avatar-btn">
                                                            <i class="fas fa-trash me-1"></i> Remove Photo
                                                        </button>
                                                        @endif
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Profile Details Section -->
                                    <div class="col-lg-8">
                                        <div class="card border-default shadow-sm h-100">
                                            <div class="card-header bg-surface border-default">
                                                <h6 class="mb-0 fw-semibold">Profile Details</h6>
                                            </div>
                                            <div class="card-body">
                                                <form method="POST" action="{{ route('admin.profile.update') }}" id="profile-form" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('POST')
                                                    
                                                    <div class="mb-3">
                                                        <label for="name" class="form-label fw-medium">
                                                            <i class="fas fa-user me-1"></i> Full Name
                                                        </label>
                                                        <input type="text" 
                                                               class="form-control border-default rounded-pill ps-3 py-2 @error('name') is-invalid @enderror" 
                                                               id="name" 
                                                               name="name" 
                                                               value="{{ old('name', $user->name) }}" 
                                                               required>
                                                        @error('name')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="email" class="form-label fw-medium">
                                                            <i class="fas fa-envelope me-1"></i> Email Address
                                                        </label>
                                                        <input type="email" 
                                                               class="form-control border-default rounded-pill ps-3 py-2 @error('email') is-invalid @enderror" 
                                                               id="email" 
                                                               name="email" 
                                                               value="{{ old('email', $user->email) }}" 
                                                               required>
                                                        @error('email')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="date_of_birth" class="form-label fw-medium">
                                                            <i class="fas fa-calendar-alt me-1"></i> Date of Birth
                                                        </label>
                                                        <input type="date" 
                                                               class="form-control border-default rounded-pill ps-3 py-2 @error('date_of_birth') is-invalid @enderror" 
                                                               id="date_of_birth" 
                                                               name="date_of_birth" 
                                                               max="{{ date('Y-m-d') }}"
                                                               value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
                                                        @error('date_of_birth')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="current_password" class="form-label fw-medium">
                                                            <i class="fas fa-lock me-1"></i> Current Password
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="password" 
                                                                   class="form-control border-default rounded-pill ps-3 py-2 @error('current_password') is-invalid @enderror" 
                                                                   id="current_password" 
                                                                   name="current_password">
                                                            <button class="btn btn-outline-secondary rounded-pill toggle-password" type="button" data-target="current_password">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                        <div class="form-text">Required to make changes to your profile</div>
                                                        @error('current_password')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="password" class="form-label fw-medium">
                                                            <i class="fas fa-key me-1"></i> New Password
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="password" 
                                                                   class="form-control border-default rounded-pill ps-3 py-2 @error('password') is-invalid @enderror" 
                                                                   id="password" 
                                                                   name="password">
                                                            <button class="btn btn-outline-secondary rounded-pill toggle-password" type="button" data-target="password">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                        <div class="form-text">Leave blank to keep current password</div>
                                                        @error('password')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    
                                                    <div class="mb-4">
                                                        <label for="password_confirmation" class="form-label fw-medium">
                                                            <i class="fas fa-key me-1"></i> Confirm New Password
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="password" 
                                                                   class="form-control border-default rounded-pill ps-3 py-2" 
                                                                   id="password_confirmation" 
                                                                   name="password_confirmation">
                                                            <button class="btn btn-outline-secondary rounded-pill toggle-password" type="button" data-target="password_confirmation">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between">
                                                        <span></span>
                                                        <button type="submit" class="btn btn-theme rounded-pill px-4 py-2">
                                                            <i class="fas fa-save me-2"></i>Update Profile
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<!-- Password visibility toggle script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                targetInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Form submission confirmation
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;
        
        if (password && password !== confirmPassword) {
            e.preventDefault();
            alert('Password confirmation does not match.');
            return false;
        }
    });
    
    // Avatar upload handling
    document.getElementById('avatar-upload').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            // Validate file type
            const file = this.files[0];
            const fileType = file.type;
            const validImageTypes = ['image/gif', 'image/jpeg', 'image/png', 'image/jpg'];
            
            if (!validImageTypes.includes(fileType)) {
                alert('Please select a valid image file (JPEG, PNG, GIF).');
                this.value = '';
                return;
            }
            
            // Validate file size (2MB max)
            const fileSize = file.size / 1024 / 1024; // in MB
            if (fileSize > 2) {
                alert('File size exceeds 2MB. Please choose a smaller file.');
                this.value = '';
                return;
            }
            
            // Submit the form
            document.getElementById('avatar-form').submit();
        }
    });
    
    // Remove avatar handling
    const removeAvatarBtn = document.getElementById('remove-avatar-btn');
    if (removeAvatarBtn) {
        removeAvatarBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to remove your profile picture?')) {
                // Create a form to submit the removal request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.profile.avatar.remove") }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    
    // Change password button functionality
    document.getElementById('change-password-btn').addEventListener('click', function() {
        // Scroll to the password fields
        document.getElementById('current_password').scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Focus on the current password field
        document.getElementById('current_password').focus();
        
        // Add a highlight effect to the password fields
        const passwordFields = document.querySelectorAll('#current_password, #password, #password_confirmation');
        passwordFields.forEach(field => {
            field.classList.add('border-primary');
            setTimeout(() => {
                field.classList.remove('border-primary');
            }, 2000);
        });
    });
    
    // Refresh button functionality
    document.getElementById('refresh-btn').addEventListener('click', function() {
        location.reload();
    });
});
</script>
@endsection