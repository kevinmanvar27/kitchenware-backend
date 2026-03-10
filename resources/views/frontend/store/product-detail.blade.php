@extends('frontend.layouts.app')

@section('title', $product->name . ' - ' . $vendor->store_name)

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('store.show', $vendor->store_slug) }}" class="text-decoration-none">{{ $vendor->store_name }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('store.products', $vendor->store_slug) }}" class="text-decoration-none">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 30) }}</li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <!-- Main Image -->
                    <div class="main-image-container mb-3">
                        @if($product->mainPhoto)
                            <img src="{{ $product->mainPhoto->url }}" alt="{{ $product->name }}" class="img-fluid rounded w-100" id="main-product-image" style="max-height: 500px; object-fit: contain;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 400px;">
                                <i class="fas fa-image fa-5x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Gallery Thumbnails -->
                    @if($product->gallery_photos && count($product->gallery_photos) > 0)
                        <div class="row g-2">
                            @if($product->mainPhoto)
                                <div class="col-3">
                                    <img src="{{ $product->mainPhoto->url }}" alt="{{ $product->name }}" 
                                         class="img-fluid rounded thumbnail-image active" 
                                         style="height: 80px; width: 100%; object-fit: cover; cursor: pointer; border: 2px solid var(--theme-color);"
                                         onclick="changeMainImage(this, '{{ $product->mainPhoto->url }}')">
                                </div>
                            @endif
                            @foreach($product->gallery_photos as $photo)
                                <div class="col-3">
                                    <img src="{{ $photo['url'] }}" alt="{{ $product->name }}" 
                                         class="img-fluid rounded thumbnail-image" 
                                         style="height: 80px; width: 100%; object-fit: cover; cursor: pointer; border: 2px solid transparent;"
                                         onclick="changeMainImage(this, '{{ $photo['url'] }}')" onerror="this.parentElement.style.display='none';">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <!-- Store Badge -->
                    <a href="{{ route('store.show', $vendor->store_slug) }}" class="text-decoration-none">
                        <span class="badge bg-light text-dark mb-3">
                            <img src="{{ $vendor->store_logo_url }}" alt="{{ $vendor->store_name }}" class="rounded-circle me-1" style="width: 20px; height: 20px; object-fit: cover;">
                            {{ $vendor->store_name }}
                        </span>
                    </a>
                    
                    <h2 class="fw-bold mb-3">{{ $product->name }}</h2>
                    
                    <!-- Price -->
                    <div class="mb-4">
                        @if($product->selling_price)
                            <span class="h3 fw-bold text-theme">₹{{ number_format($product->selling_price, 2) }}</span>
                        @endif
                        @if($product->mrp && $product->mrp > $product->selling_price)
                            <span class="text-muted text-decoration-line-through ms-2">₹{{ number_format($product->mrp, 2) }}</span>
                            @php
                                $discount = round((($product->mrp - $product->selling_price) / $product->mrp) * 100);
                            @endphp
                            <span class="badge bg-danger ms-2">{{ $discount }}% OFF</span>
                        @endif
                    </div>
                    
                    <!-- Short Description -->
                    @if($product->short_description)
                        <div class="text-muted mb-4">{!! $product->short_description !!}</div>
                    @endif
                    
                    <!-- Product Info -->
                    <div class="mb-4">
                        <div class="row g-3">
                            @if($product->sku)
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-barcode text-muted me-2"></i>
                                        <span class="small"><strong>SKU:</strong> {{ $product->sku }}</span>
                                    </div>
                                </div>
                            @endif
                            @if($product->product_type)
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-tag text-muted me-2"></i>
                                        <span class="small"><strong>Type:</strong> {{ ucfirst($product->product_type) }}</span>
                                    </div>
                                </div>
                            @endif
                            @if($product->stock_quantity !== null)
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-box text-muted me-2"></i>
                                        <span class="small">
                                            <strong>Stock:</strong> 
                                            @if($product->stock_quantity > 0)
                                                <span class="text-success">In Stock ({{ $product->stock_quantity }})</span>
                                            @else
                                                <span class="text-danger">Out of Stock</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Variations -->
                    @if($product->product_type === 'variable' && $product->variations && $product->variations->count() > 0)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Available Variations</h6>
                            <div class="row g-2">
                                @foreach($product->variations as $variation)
                                    <div class="col-12">
                                        <div class="border rounded p-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                @if($variation->formatted_attributes)
                                                    @foreach($variation->formatted_attributes as $attrName => $attrValue)
                                                        <span class="badge bg-light text-dark me-1">
                                                            {{ $attrName }}: {{ $attrValue }}
                                                        </span>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                @if($variation->selling_price)
                                                    <span class="fw-bold text-theme">₹{{ number_format($variation->selling_price, 2) }}</span>
                                                @endif
                                                @if($variation->stock_quantity > 0)
                                                    <span class="badge bg-success ms-2">In Stock</span>
                                                @else
                                                    <span class="badge bg-danger ms-2">Out of Stock</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Categories -->
                    @if($product->categories && $product->categories->count() > 0)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Categories</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($product->categories as $category)
                                    <a href="{{ route('store.products', ['slug' => $vendor->store_slug, 'category' => $category->id]) }}" class="badge bg-light text-dark text-decoration-none">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Contact Store -->
                    <div class="d-grid gap-2">
                        @if($vendor->business_phone)
                            <a href="tel:{{ $vendor->business_phone }}" class="btn btn-theme btn-lg rounded-pill">
                                <i class="fas fa-phone me-2"></i>Contact Store
                            </a>
                        @endif
                        @if($vendor->business_email)
                            <a href="mailto:{{ $vendor->business_email }}?subject=Inquiry about {{ $product->name }}" class="btn btn-outline-theme btn-lg rounded-pill">
                                <i class="fas fa-envelope me-2"></i>Send Email
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Description -->
    @if($product->description)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">Product Description</h5>
                    </div>
                    <div class="card-body">
                        {!! $product->description !!}
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="mt-5">
            <h4 class="fw-bold mb-4">More from {{ $vendor->store_name }}</h4>
            <div class="row g-4">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100 product-card">
                            <a href="{{ route('store.product', ['slug' => $vendor->store_slug, 'productSlug' => $relatedProduct->slug]) }}" class="text-decoration-none">
                                <div class="position-relative">
                                    @if($relatedProduct->mainPhoto)
                                        <img src="{{ $relatedProduct->mainPhoto->url }}" class="card-img-top" alt="{{ $relatedProduct->name }}" style="height: 180px; object-fit: cover;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                            <i class="fas fa-image fa-2x text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title text-dark fw-bold mb-2">{{ Str::limit($relatedProduct->name, 30) }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($relatedProduct->selling_price)
                                            <span class="fw-bold text-theme">₹{{ number_format($relatedProduct->selling_price, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<style>
    .text-theme {
        color: var(--theme-color);
    }
    
    .btn-theme {
        background-color: var(--theme-color);
        border-color: var(--theme-color);
        color: #fff;
    }
    
    .btn-theme:hover {
        background-color: var(--theme-color);
        border-color: var(--theme-color);
        color: #fff;
        opacity: 0.9;
    }
    
    .btn-outline-theme {
        border-color: var(--theme-color);
        color: var(--theme-color);
    }
    
    .btn-outline-theme:hover {
        background-color: var(--theme-color);
        border-color: var(--theme-color);
        color: #fff;
    }
    
    .product-card {
        transition: all 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    
    .thumbnail-image {
        transition: all 0.2s ease;
    }
    
    .thumbnail-image:hover {
        border-color: var(--theme-color) !important;
    }
    
    .thumbnail-image.active {
        border-color: var(--theme-color) !important;
    }
</style>

<script>
function changeMainImage(element, url) {
    // Update main image
    document.getElementById('main-product-image').src = url;
    
    // Update active state on thumbnails
    document.querySelectorAll('.thumbnail-image').forEach(img => {
        img.classList.remove('active');
        img.style.borderColor = 'transparent';
    });
    element.classList.add('active');
    element.style.borderColor = 'var(--theme-color)';
}
</script>
@endsection
