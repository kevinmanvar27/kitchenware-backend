@extends('admin.layouts.app')

@section('title', isset($isStaff) && $isStaff ? 'Add Staff Member' : 'Add User')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => isset($isStaff) && $isStaff ? 'Add Staff Member' : 'Add User'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h4 class="card-title mb-0 fw-bold">{{ isset($isStaff) && $isStaff ? 'Add New Staff Member' : 'Add New User' }}</h4>
                                <p class="mb-0 text-muted">{{ isset($isStaff) && $isStaff ? 'Create a new staff account (Admin, Super Admin, or Editor)' : 'Create a new user account with role assignment' }}</p>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <form action="{{ route('admin.users.store') }}" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="row">
                                        <div class="col-md-12 text-center mb-4">
                                            <div class="position-relative d-inline-block">
                                                <img id="avatar-preview" src="https://ui-avatars.com/api/?name=New+User&background=random" 
                                                     class="rounded-circle border border-3 border-primary" width="100" height="100" alt="Avatar Preview">
                                                <div class="position-absolute bottom-0 end-0 bg-primary rounded-circle p-1">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <label for="avatar" class="form-label fw-medium">Profile Picture</label>
                                                <input type="file" class="form-control form-control-sm mx-auto @error('avatar') is-invalid @enderror" 
                                                       id="avatar" name="avatar" accept="image/*" style="max-width: 200px;">
                                                <div class="form-text">Optional. Max 2MB. JPG, PNG, GIF.</div>
                                                @error('avatar')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="name" class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </span>
                                                    <input type="text" class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2 @error('name') is-invalid @enderror" 
                                                           id="name" name="name" value="{{ old('name') }}" placeholder="Enter full name" required>
                                                </div>
                                                @error('name')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="email" class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-envelope text-muted"></i>
                                                    </span>
                                                    <input type="email" class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2 @error('email') is-invalid @enderror" 
                                                           id="email" name="email" value="{{ old('email') }}" placeholder="Enter email address" required>
                                                </div>
                                                @error('email')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="password" class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-lock text-muted"></i>
                                                    </span>
                                                    <input type="password" class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2 @error('password') is-invalid @enderror" 
                                                           id="password" name="password" placeholder="Enter password" required>
                                                </div>
                                                <div class="form-text ms-4">Password must be at least 8 characters long.</div>
                                                @error('password')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="password_confirmation" class="form-label fw-medium">Confirm Password <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-lock text-muted"></i>
                                                    </span>
                                                    <input type="password" class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2" 
                                                           id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="user_role" class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-user-tag text-muted"></i>
                                                    </span>
                                                    <select class="form-select border-0 border-bottom rounded-end-pill ps-0 py-2 @error('user_role') is-invalid @enderror" 
                                                            id="user_role" name="user_role" required>
                                                        <option value="">Select Role</option>
                                                        @if(isset($isStaff) && $isStaff)
                                                            {{-- Only show staff roles when creating staff --}}
                                                            <option value="super_admin" {{ (old('user_role') ?? $role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                                            <option value="admin" {{ (old('user_role') ?? $role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                                            <option value="editor" {{ (old('user_role') ?? $role) == 'editor' ? 'selected' : '' }}>Editor</option>
                                                        @else
                                                            {{-- Show all roles when creating regular user --}}
                                                            <option value="super_admin" {{ (old('user_role') ?? $role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                                            <option value="admin" {{ (old('user_role') ?? $role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                                            <option value="editor" {{ (old('user_role') ?? $role) == 'editor' ? 'selected' : '' }}>Editor</option>
                                                            <option value="user" {{ (old('user_role') ?? $role) == 'user' ? 'selected' : '' }}>User</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                @error('user_role')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="date_of_birth" class="form-label fw-medium">Date of Birth</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-calendar text-muted"></i>
                                                    </span>
                                                    <input type="date" class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2 @error('date_of_birth') is-invalid @enderror" 
                                                           id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                                </div>
                                                @error('date_of_birth')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="address" class="form-label fw-medium">Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                                    </span>
                                                    <input type="text" class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2 @error('address') is-invalid @enderror" 
                                                           id="address" name="address" value="{{ old('address') }}" placeholder="Enter address">
                                                </div>
                                                @error('address')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>  
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="mobile_number" class="form-label fw-medium">Mobile Number</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-phone text-muted"></i>
                                                    </span>
                                                    <input type="text" class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2 @error('mobile_number') is-invalid @enderror" 
                                                           id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}" placeholder="Enter mobile number">
                                                </div>
                                                @error('mobile_number')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="status" class="form-label fw-medium">User Status <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-user-check text-muted"></i>
                                                    </span>
                                                    <select class="form-select border-0 border-bottom rounded-end-pill ps-0 py-2 @error('status') is-invalid @enderror" 
                                                            id="status" name="status" required>
                                                        <option value="Pending" {{ old('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="Under review" {{ old('status') == 'Under review' ? 'selected' : '' }}>Under review</option>
                                                        <option value="Approved" {{ old('status', 'Approved') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                                        <option value="Suspend" {{ old('status') == 'Suspend' ? 'selected' : '' }}>Suspend</option>
                                                        <option value="Block" {{ old('status') == 'Block' ? 'selected' : '' }}>Block</option>
                                                    </select>
                                                </div>
                                                <div class="form-text ms-4">
                                                    <small>
                                                        <strong>Pending:</strong> User awaiting approval<br>
                                                        <strong>Under review:</strong> User account being reviewed<br>
                                                        <strong>Approved:</strong> User can access the system<br>
                                                        <strong>Suspend:</strong> Temporarily disable user access<br>
                                                        <strong>Block:</strong> Permanently block user access
                                                    </small>
                                                </div>
                                                @error('status')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="discount_percentage" class="form-label fw-medium">Discount Percentage</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-percent text-muted"></i>
                                                    </span>
                                                    <input type="number" class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2 @error('discount_percentage') is-invalid @enderror" 
                                                           id="discount_percentage" name="discount_percentage" 
                                                           value="{{ old('discount_percentage', 0) }}" 
                                                           min="0" max="100" step="0.01" placeholder="Enter discount percentage">
                                                </div>
                                                <div class="form-text ms-4">Enter a discount percentage for this user (0-100%)</div>
                                                @error('discount_percentage')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-5">
                                        <a href="{{ (isset($isStaff) && $isStaff) || $role !== 'user' ? route('admin.users.staff') : route('admin.users.index') }}" class="btn btn-light rounded-pill px-4">
                                            <i class="fas fa-arrow-left me-2"></i> Back
                                        </a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i> {{ isset($isStaff) && $isStaff ? 'Create Staff Member' : 'Create User' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<script>
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Check if file is an image
        if (!file.type.match('image.*')) {
            alert('Please select an image file (JPEG, PNG, GIF).');
            e.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endsection