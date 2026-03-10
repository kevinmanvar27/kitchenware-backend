@extends('admin.layouts.app')

@section('title', 'Edit Page')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Edit Page'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Edit Page</h4>
                                    <p class="mb-0 text-muted">Edit an existing page</p>
                                </div>
                                <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Pages
                                </a>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <form action="{{ route('admin.pages.update', $page) }}" method="POST" id="page-form">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="title" class="form-label">Page Title</label>
                                                <input type="text" class="form-control rounded-pill" id="title" name="title" value="{{ old('title', $page->title) }}" required>
                                                @error('title')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="slug" class="form-label">Page Slug</label>
                                                <input type="text" class="form-control rounded-pill" id="slug" name="slug" value="{{ old('slug', $page->slug) }}" required>
                                                @error('slug')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="priority" class="form-label">Priority</label>
                                                <input type="number" class="form-control rounded-pill" id="priority" name="priority" value="{{ old('priority', $page->priority) }}" min="0">
                                                <div class="form-text">Lower numbers have higher priority</div>
                                                @error('priority')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="content" class="form-label">Page Content</label>
                                                <textarea class="form-control" id="content" name="content" rows="10">{{ old('content', $page->content) }}</textarea>
                                                @error('content')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="card border rounded-3">
                                                <div class="card-header bg-light py-2">
                                                    <h6 class="mb-0">Publish</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $page->active) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="active">Active</label>
                                                    </div>
                                                    
                                                    <button type="submit" class="btn btn-theme w-100 rounded-pill">
                                                        <i class="fas fa-save me-2"></i>Update Page
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
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

<!-- Include CKEditor from CDN -->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
  CKEDITOR.replace('content', {
    versionCheck: false
  });
</script>
@endsection