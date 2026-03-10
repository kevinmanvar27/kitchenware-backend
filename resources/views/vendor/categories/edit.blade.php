@extends('vendor.layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Edit Category'])
            
            @section('page-title', 'Edit Category')
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Edit Category</h4>
                                    <p class="mb-0 text-muted">{{ $category->name }}</p>
                                </div>
                                <a href="{{ route('vendor.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </a>
                            </div>
                            
                            <div class="card-body">
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
                                
                                <form action="{{ route('vendor.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-pill px-4 py-2" id="name" name="name" value="{{ old('name', $category->name) }}" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="slug" class="form-label fw-bold">Slug</label>
                                        <input type="text" class="form-control rounded-pill px-4 py-2" id="slug" name="slug" value="{{ old('slug', $category->slug) }}">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="description" class="form-label fw-bold">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="parent_id" class="form-label fw-bold">Parent Category</label>
                                        <select class="form-select rounded-pill" id="parent_id" name="parent_id">
                                            <option value="">None (Top Level)</option>
                                            @foreach($parentCategories ?? [] as $parent)
                                                @if($parent->id !== $category->id)
                                                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                                    {{ $parent->name }}
                                                </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="image" class="form-label fw-bold">Category Image</label>
                                        @if($category->image)
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="img-thumbnail" style="max-height: 100px;">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <div class="form-text">Leave empty to keep current image</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="{{ route('vendor.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Update Category
                                        </button>
                                    </div>
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
