@extends('admin.layouts.app')

@section('title', 'Create Permission')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Create Permission'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h4 class="card-title mb-0 fw-bold">Create New Permission</h4>
                                <p class="mb-0 text-muted">Define a new system permission</p>
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
                                
                                <form action="{{ route('admin.permissions.store') }}" method="POST" class="needs-validation" novalidate>
                                    @csrf
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="name" class="form-label fw-medium">Name <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                                                        <i class="fas fa-key text-muted"></i>
                                                    </span>
                                                    <input type="text" class="form-control border-0 border-bottom rounded-end-pill ps-0 py-2 @error('name') is-invalid @enderror" 
                                                           id="name" name="name" value="{{ old('name') }}" placeholder="Enter permission name" required>
                                                </div>
                                                <div class="form-text ms-4">Unique identifier for the permission (e.g., manage_users)</div>
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
                                                           id="display_name" name="display_name" value="{{ old('display_name') }}" placeholder="Enter display name" required>
                                                </div>
                                                <div class="form-text ms-4">Human-readable name for the permission</div>
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
                                                              id="description" name="description" placeholder="Enter permission description" rows="3">{{ old('description') }}</textarea>
                                                </div>
                                                <div class="form-text ms-4">Optional description of the permission's purpose</div>
                                                @error('description')
                                                    <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mt-5">
                                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-light rounded-pill px-4">
                                            <i class="fas fa-arrow-left me-2"></i> Back to Permissions
                                        </a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i> Create Permission
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