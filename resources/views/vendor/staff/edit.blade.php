@extends('vendor.layouts.app')

@section('title', 'Edit Staff Member')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Edit Staff Member'])
            
            <div class="pt-4 pb-2 mb-3">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-0 fw-bold">Edit Staff Member</h4>
                                <p class="text-muted mb-0 small">Update staff account details and permissions</p>
                            </div>
                            <a href="{{ route('vendor.staff.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <form action="{{ route('vendor.staff.update', $staff->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <!-- Personal Information -->
                                <div class="col-lg-6">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="fas fa-user me-2"></i>Personal Information
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-pill px-4" id="name" name="name" value="{{ old('name', $staff->user->name) }}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control rounded-pill px-4" id="email" name="email" value="{{ old('email', $staff->user->email) }}" required>
                                        <div class="form-text">This will be used for login at <strong>{{ route('vendor.staff.login') }}</strong></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mobile_number" class="form-label">Mobile Number</label>
                                        <input type="text" class="form-control rounded-pill px-4" id="mobile_number" name="mobile_number" value="{{ old('mobile_number', $staff->user->mobile_number) }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control rounded-pill px-4" id="password" name="password">
                                        <div class="form-text">Leave blank to keep current password</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control rounded-pill px-4" id="password_confirmation" name="password_confirmation">
                                    </div>
                                </div>
                                
                                <!-- Role & Permissions -->
                                <div class="col-lg-6">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="fas fa-shield-alt me-2"></i>Role & Permissions
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select class="form-select rounded-pill px-4" id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            <option value="manager" {{ old('role', $staff->role) == 'manager' ? 'selected' : '' }}>Manager</option>
                                            <option value="sales" {{ old('role', $staff->role) == 'sales' ? 'selected' : '' }}>Sales Staff</option>
                                            <option value="inventory" {{ old('role', $staff->role) == 'inventory' ? 'selected' : '' }}>Inventory Staff</option>
                                            <option value="support" {{ old('role', $staff->role) == 'support' ? 'selected' : '' }}>Support Staff</option>
                                            <option value="delivery" {{ old('role', $staff->role) == 'delivery' ? 'selected' : '' }}>Delivery Staff</option>
                                            <option value="accountant" {{ old('role', $staff->role) == 'accountant' ? 'selected' : '' }}>Accountant</option>
                                        </select>
                                    </div>
                                    
                                    @php
                                        $staffPermissions = $staff->permissions ?? [];
                                    @endphp
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Permissions</label>
                                        <div class="border rounded-3 p-3">
                                            <div class="row">
                                                @php
                                                    $permissionIcons = [
                                                        'dashboard' => 'fa-home',
                                                        'profile' => 'fa-user',
                                                        'store_settings' => 'fa-store',
                                                        'products' => 'fa-box',
                                                        'variations' => 'fa-layer-group',
                                                        'attributes' => 'fa-sliders-h',
                                                        'categories' => 'fa-tags',
                                                        'invoices' => 'fa-file-invoice',
                                                        'pending_bills' => 'fa-file-invoice-dollar',
                                                        'leads' => 'fa-user-plus',
                                                        'customers' => 'fa-user-friends',
                                                        'staff' => 'fa-users',
                                                        'salary' => 'fa-money-bill-wave',
                                                        'attendance' => 'fa-calendar-check',
                                                        'reports' => 'fa-chart-bar',
                                                        'analytics' => 'fa-chart-line',
                                                        'coupons' => 'fa-ticket-alt',
                                                        'banners' => 'fa-image',
                                                        'push_notifications' => 'fa-bell',
                                                        'activity_logs' => 'fa-history',
                                                        'view_tasks' => 'fa-tasks',
                                                    ];
                                                @endphp
                                                @foreach($permissions as $key => $label)
                                                <div class="col-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}" id="perm_{{ $key }}" {{ in_array($key, old('permissions', $staffPermissions)) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="perm_{{ $key }}">
                                                            <i class="fas {{ $permissionIcons[$key] ?? 'fa-check' }} me-1 text-muted"></i>{{ $label }}
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="form-text">Select the areas this staff member can access</div>
                                    </div>
                                    
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill" onclick="selectAllPermissions()">
                                            <i class="fas fa-check-double me-1"></i>Select All
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary rounded-pill" onclick="clearAllPermissions()">
                                            <i class="fas fa-times me-1"></i>Clear All
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Status -->
                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="fas fa-toggle-on me-2"></i>Status
                                    </h6>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active Account</label>
                                    </div>
                                    <div class="form-text">Inactive accounts cannot log in</div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('vendor.staff.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-theme rounded-pill px-4">
                                    <i class="fas fa-save me-2"></i>Update Staff Member
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function selectAllPermissions() {
        document.querySelectorAll('input[name="permissions[]"]').forEach(function(checkbox) {
            checkbox.checked = true;
        });
    }
    
    function clearAllPermissions() {
        document.querySelectorAll('input[name="permissions[]"]').forEach(function(checkbox) {
            checkbox.checked = false;
        });
    }
</script>
@endsection
