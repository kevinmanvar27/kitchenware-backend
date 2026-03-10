@extends('admin.layouts.app')

@section('title', 'View Product')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'View Product'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Product Details</h4>
                                    <p class="mb-0 text-muted">View product information</p>
                                </div>
                                <div class="d-flex gap-2">
                                    @can('update', $product)
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-theme rounded-pill px-4">
                                        <i class="fas fa-edit me-2"></i> Edit Product
                                    </a>
                                    @endcan
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                        <i class="fas fa-arrow-left me-2"></i> Back to Products
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="mb-4">
                                            <h5 class="fw-bold mb-3">Basic Information</h5>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="text-muted small mb-1">Product Name</label>
                                                    <div class="fw-medium">{{ $product->name }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="text-muted small mb-1">Status</label>
                                                    <div>
                                                        @if($product->status === 'published')
                                                            <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                                                                Published
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                                                                Draft
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="text-muted small mb-1">MRP</label>
                                                    <div class="fw-medium">₹{{ number_format($product->mrp, 2) }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="text-muted small mb-1">Selling Price</label>
                                                    <div class="fw-medium">₹{{ number_format($product->selling_price ?? 0, 2) }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="text-muted small mb-1">Stock Status</label>
                                                    <div>
                                                        @if($product->in_stock)
                                                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                                                In Stock ({{ $product->stock_quantity }})
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill px-3 py-2">
                                                                Out of Stock
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="text-muted small mb-1">Created</label>
                                                    <div class="fw-medium">
                                                        @if($product->created_at)
                                                            {{ $product->created_at->format('F j, Y \a\t g:i A') }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h5 class="fw-bold mb-3">Description</h5>
                                            <div class="border rounded-3 p-3">
                                                @if($product->description)
                                                    {!! $product->description !!}
                                                @else
                                                    <span class="text-muted">No description provided</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h5 class="fw-bold mb-3">SEO Settings</h5>
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="text-muted small mb-1">Meta Title</label>
                                                    <div class="fw-medium">
                                                        @if($product->meta_title)
                                                            {{ $product->meta_title }}
                                                        @else
                                                            <span class="text-muted">Not set</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="text-muted small mb-1">Meta Description</label>
                                                    <div class="fw-medium">
                                                        @if($product->meta_description)
                                                            {{ $product->meta_description }}
                                                        @else
                                                            <span class="text-muted">Not set</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="text-muted small mb-1">Meta Keywords</label>
                                                    <div class="fw-medium">
                                                        @if($product->meta_keywords)
                                                            {{ $product->meta_keywords }}
                                                        @else
                                                            <span class="text-muted">Not set</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-4">
                                        <div class="mb-4">
                                            <h5 class="fw-bold mb-3">Main Photo</h5>
                                            <div class="border rounded-3 p-3 text-center">
                                                @if($product->mainPhoto)
                                                    <img src="{{ $product->mainPhoto->url }}" class="img-fluid rounded" alt="{{ $product->name }}">
                                                @else
                                                    <div class="py-5">
                                                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted mb-0">No main photo</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h5 class="fw-bold mb-3">Gallery Photos</h5>
                                            <div class="border rounded-3 p-3">
                                                @if($product->gallery_photos && count($product->gallery_photos) > 0)
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($product->gallery_photos as $photo)
                                                            <div>
                                                                <img src="{{ $photo['url'] }}" class="img-fluid rounded" alt="Gallery image" style="height: 80px; object-fit: cover;" onerror="this.style.display='none';">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="py-3 text-center">
                                                        <i class="fas fa-images fa-2x text-muted mb-2"></i>
                                                        <p class="text-muted mb-0">No gallery photos</p>
                                                    </div>
                                                @endif
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
@endsection