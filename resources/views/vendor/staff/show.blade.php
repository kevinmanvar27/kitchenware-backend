@extends('vendor.layouts.app')

@section('title', 'Staff Details')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Staff Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-0 fw-bold">Staff Details</h4>
                                <p class="text-muted mb-0 small">View staff member information</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('vendor.staff.edit', $staff->id) }}" class="btn btn-theme rounded-pill px-4">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a>
                                <a href="{{ route('vendor.staff.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <!-- Staff Info -->
                            <div class="col-lg-6">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 80px; height: 80px;">
                                        <span class="text-primary fw-bold fs-2">{{ strtoupper(substr($staff->user->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <h4 class="mb-1">{{ $staff->user->name }}</h4>
                                        <span class="badge bg-{{ $staff->is_active ? 'success' : 'danger' }} rounded-pill">
                                            {{ $staff->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <span class="badge bg-info rounded-pill ms-1">{{ ucfirst($staff->role) }}</span>
                                    </div>
                                </div>
                                
                                <h6 class="fw-bold mb-3 text-primary">
                                    <i class="fas fa-user me-2"></i>Personal Information
                                </h6>
                                
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" style="width: 150px;">Email</td>
                                        <td class="fw-medium">{{ $staff->user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Mobile</td>
                                        <td class="fw-medium">{{ $staff->user->mobile_number ?? 'Not provided' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Role</td>
                                        <td class="fw-medium">{{ ucfirst($staff->role) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Status</td>
                                        <td>
                                            @if($staff->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Joined</td>
                                        <td class="fw-medium">{{ $staff->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Last Updated</td>
                                        <td class="fw-medium">{{ $staff->updated_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Permissions -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold mb-3 text-primary">
                                    <i class="fas fa-shield-alt me-2"></i>Permissions
                                </h6>
                                
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
                                    $staffPermissions = $staff->permissions ?? [];
                                @endphp
                                
                                <div class="border rounded-3 p-3">
                                    <div class="row">
                                        @foreach($permissions as $key => $label)
                                            <div class="col-6 mb-2">
                                                <div class="d-flex align-items-center">
                                                    @if(in_array($key, $staffPermissions))
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                    @else
                                                        <i class="fas fa-times-circle text-danger me-2"></i>
                                                    @endif
                                                    <i class="fas {{ $permissionIcons[$key] ?? 'fa-check' }} me-2 text-muted"></i>
                                                    <span class="{{ in_array($key, $staffPermissions) ? '' : 'text-muted' }}">{{ $label }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="fas fa-history me-2"></i>Activity Summary
                                    </h6>
                                    <div class="border rounded-3 p-3">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="text-muted small">Account Age</div>
                                                <div class="fw-bold">{{ $staff->created_at->diffForHumans(null, true) }}</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-muted small">Last Login</div>
                                                <div class="fw-bold">{{ $staff->user->last_login_at ? $staff->user->last_login_at->diffForHumans() : 'Never' }}</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-muted small">Permissions</div>
                                                <div class="fw-bold">{{ count($staffPermissions) }} / {{ count($permissions) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Quick Actions -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0">Quick Actions</h6>
                            </div>
                            <div class="d-flex gap-2">
                                <form action="{{ route('vendor.staff.toggle-status', $staff->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-{{ $staff->is_active ? 'warning' : 'success' }} rounded-pill px-4">
                                        <i class="fas fa-{{ $staff->is_active ? 'ban' : 'check' }} me-2"></i>
                                        {{ $staff->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('vendor.staff.destroy', $staff->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection
