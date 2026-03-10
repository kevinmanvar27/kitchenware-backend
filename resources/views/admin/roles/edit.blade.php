@extends('admin.layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Edit Role'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h4 class="card-title mb-0 fw-bold">Edit Role</h4>
                                <p class="mb-0 text-muted">Update role details and permissions</p>
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
                                
                                <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="needs-validation" novalidate>
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="name" class="form-label fw-medium">Name <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-tag text-muted"></i>
                                                    </span>
                                                    <input type="text" class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2 @error('name') is-invalid @enderror" 
                                                           id="name" name="name" value="{{ old('name', $role->name) }}" placeholder="Enter role name" required>
                                                </div>
                                                <div class="form-text ms-4">Unique identifier for the role (e.g., admin, editor)</div>
                                                @error('name')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="display_name" class="form-label fw-medium">Display Name <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-font text-muted"></i>
                                                    </span>
                                                    <input type="text" class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2 @error('display_name') is-invalid @enderror" 
                                                           id="display_name" name="display_name" value="{{ old('display_name', $role->display_name) }}" placeholder="Enter display name" required>
                                                </div>
                                                <div class="form-text ms-4">Human-readable name for the role</div>
                                                @error('display_name')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-4">
                                                <label for="description" class="form-label fw-medium">Description</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill align-self-start mt-1">
                                                        <i class="fas fa-align-left text-muted"></i>
                                                    </span>
                                                    <textarea class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2 @error('description') is-invalid @enderror" 
                                                              id="description" name="description" placeholder="Enter role description" rows="3">{{ old('description', $role->description) }}</textarea>
                                                </div>
                                                <div class="form-text ms-4">Optional description of the role's purpose</div>
                                                @error('description')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-4">
                                                <label class="form-label fw-medium">Permissions</label>
                                                <div class="border rounded-3 p-3">
                                                    @if($permissions->count() > 0)
                                                        <div class="row">
                                                            @foreach($permissions as $permission)
                                                                <div class="col-md-6 mb-2">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" 
                                                                               name="permissions[]" value="{{ $permission->id }}" 
                                                                               id="permission_{{ $permission->id }}" 
                                                                               {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                                            <span class="fw-medium">{{ $permission->display_name }}</span>
                                                                            <div class="small text-muted">{{ $permission->description ?? 'No description' }}</div>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="text-center py-3">
                                                            <i class="fas fa-key fa-2x text-muted mb-2"></i>
                                                            <p class="mb-0 text-muted">No permissions available</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mt-5">
                                        <a href="{{ route('admin.roles.index') }}" class="btn btn-light rounded-pill px-4">
                                            <i class="fas fa-arrow-left me-2"></i> Back to Roles
                                        </a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i> Update Role
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
@endsection