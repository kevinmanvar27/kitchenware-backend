@extends('admin.layouts.app')

@section('title', 'Vendor Permissions - ' . $vendor->store_name)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Vendor Permissions'])
            
            <div class="pt-4 pb-2 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.vendors.index') }}">Vendors</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.vendors.show', $vendor) }}">{{ $vendor->store_name }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                    </ol>
                </nav>
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="fas fa-shield-alt me-2 text-primary"></i>Manage Permissions for {{ $vendor->store_name }}
                            </h5>
                            <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-outline-secondary rounded-pill">
                                <i class="fas fa-arrow-left me-2"></i>Back to Vendor
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.vendors.permissions.update', $vendor) }}" method="POST">
                            @csrf
                            
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Select the permissions you want to grant to this vendor. These permissions control what actions the vendor can perform in their panel.
                            </div>
                            
                            @php
                                $groupedPermissions = $permissions->groupBy(function($permission) {
                                    $parts = explode('_', $permission->name);
                                    return ucfirst(end($parts));
                                });
                            @endphp
                            
                            <div class="row">
                                @foreach($groupedPermissions as $group => $groupPermissions)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border h-100">
                                        <div class="card-header bg-light">
                                            <div class="form-check">
                                                <input class="form-check-input group-checkbox" type="checkbox" id="group-{{ Str::slug($group) }}" data-group="{{ Str::slug($group) }}">
                                                <label class="form-check-label fw-bold" for="group-{{ Str::slug($group) }}">
                                                    {{ $group }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @foreach($groupPermissions as $permission)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input permission-checkbox permission-{{ Str::slug($group) }}" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->id }}" 
                                                       id="permission-{{ $permission->id }}"
                                                       {{ in_array($permission->id, $vendorPermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                    @php
                                                        $actionParts = explode('_', $permission->name);
                                                        $action = ucfirst($actionParts[0] ?? '');
                                                    @endphp
                                                    {{ $action }}
                                                    @if($permission->description)
                                                        <small class="text-muted d-block">{{ $permission->description }}</small>
                                                    @endif
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-4 border-top">
                                <div>
                                    <button type="button" class="btn btn-outline-secondary me-2" id="select-all">
                                        <i class="fas fa-check-double me-2"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="deselect-all">
                                        <i class="fas fa-times me-2"></i>Deselect All
                                    </button>
                                </div>
                                <button type="submit" class="btn btn-theme rounded-pill px-4">
                                    <i class="fas fa-save me-2"></i>Save Permissions
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Group checkbox functionality
    document.querySelectorAll('.group-checkbox').forEach(function(groupCheckbox) {
        groupCheckbox.addEventListener('change', function() {
            const group = this.dataset.group;
            const checkboxes = document.querySelectorAll('.permission-' + group);
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = groupCheckbox.checked;
            });
        });
    });
    
    // Update group checkbox state based on individual checkboxes
    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            updateGroupCheckbox(this);
        });
    });
    
    function updateGroupCheckbox(checkbox) {
        const classes = checkbox.className.split(' ');
        const groupClass = classes.find(c => c.startsWith('permission-') && c !== 'permission-checkbox');
        if (groupClass) {
            const group = groupClass.replace('permission-', '');
            const groupCheckbox = document.getElementById('group-' + group);
            const groupPermissions = document.querySelectorAll('.' + groupClass);
            const checkedCount = document.querySelectorAll('.' + groupClass + ':checked').length;
            
            if (groupCheckbox) {
                groupCheckbox.checked = checkedCount === groupPermissions.length;
                groupCheckbox.indeterminate = checkedCount > 0 && checkedCount < groupPermissions.length;
            }
        }
    }
    
    // Initialize group checkbox states
    document.querySelectorAll('.group-checkbox').forEach(function(groupCheckbox) {
        const group = groupCheckbox.dataset.group;
        const groupPermissions = document.querySelectorAll('.permission-' + group);
        const checkedCount = document.querySelectorAll('.permission-' + group + ':checked').length;
        
        groupCheckbox.checked = checkedCount === groupPermissions.length;
        groupCheckbox.indeterminate = checkedCount > 0 && checkedCount < groupPermissions.length;
    });
    
    // Select all / Deselect all
    document.getElementById('select-all').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox, .group-checkbox').forEach(function(checkbox) {
            checkbox.checked = true;
            checkbox.indeterminate = false;
        });
    });
    
    document.getElementById('deselect-all').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox, .group-checkbox').forEach(function(checkbox) {
            checkbox.checked = false;
            checkbox.indeterminate = false;
        });
    });
});
</script>
@endpush