@extends('frontend.layouts.app')

@section('title', $vendor->store_name)

@section('content')
<div class="container-fluid px-0">
    <!-- Store Banner -->
    <div class="store-banner position-relative" style="height: 300px; overflow: hidden;">
        @if($vendor->store_banner_url)
            <div class="w-100 h-100" style="background: linear-gradient(135deg, var(--theme-color) 0%, #333 100%);">
                <img src="{{ $vendor->store_banner_url }}" alt="{{ $vendor->store_name }}" class="w-100 h-100" style="object-fit: cover; opacity: 0.7;">
            </div>
        @else
            <!-- Default gradient banner when no banner is uploaded -->
            <div class="w-100 h-100" style="background: linear-gradient(135deg, var(--theme-color) 0%, #1a1a2e 50%, #16213e 100%);">
                <div class="position-absolute w-100 h-100" style="background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><defs><pattern id=%22grid%22 width=%2210%22 height=%2210%22 patternUnits=%22userSpaceOnUse%22><path d=%22M 10 0 L 0 0 0 10%22 fill=%22none%22 stroke=%22rgba(255,255,255,0.05)%22 stroke-width=%220.5%22/></pattern></defs><rect width=%22100%22 height=%22100%22 fill=%22url(%23grid)%22/></svg>'); opacity: 0.5;"></div>
            </div>
        @endif
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.3);">
            <div class="text-center text-white">
                <img src="{{ $vendor->store_logo_url }}" alt="{{ $vendor->store_name }}" class="rounded-circle mb-3 border border-3 border-white shadow-lg" style="width: 120px; height: 120px; object-fit: cover;">
                <h1 class="fw-bold mb-2">{{ $vendor->store_name }}</h1>
                @if($vendor->store_description)
                    <div class="mb-0 opacity-75">{!! Str::limit($vendor->store_description, 150) !!}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Store Info Bar -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex flex-wrap gap-4">
                                @if($vendor->city || $vendor->state)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-map-marker-alt text-theme me-2"></i>
                                        <span>{{ $vendor->city }}{{ $vendor->city && $vendor->state ? ', ' : '' }}{{ $vendor->state }}</span>
                                    </div>
                                @endif
                                @if($vendor->business_phone)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-phone text-theme me-2"></i>
                                        <span>{{ $vendor->business_phone }}</span>
                                    </div>
                                @endif
                                @if($vendor->business_email)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-envelope text-theme me-2"></i>
                                        <span>{{ $vendor->business_email }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <a href="{{ route('store.products', $vendor->store_slug) }}" class="btn btn-theme rounded-pill px-4">
                                <i class="fas fa-shopping-bag me-2"></i>View All Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Categories Section -->
    @if($categories->count() > 0)
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Shop by Category</h3>
        </div>
        <div class="row g-4">
            @foreach($categories as $category)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('store.products', ['slug' => $vendor->store_slug, 'category' => $category->id]) }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 category-card">
                            <div class="card-body text-center p-4">
                                @if($category->image_url)
                                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                        <i class="fas fa-folder fa-2x text-muted"></i>
                                    </div>
                                @endif
                                <h6 class="fw-bold text-dark mb-0">{{ $category->name }}</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
    @endif
    
    <!-- Featured Products Section -->
    @if($featuredProducts->count() > 0)
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Featured Products</h3>
            <a href="{{ route('store.products', $vendor->store_slug) }}" class="btn btn-outline-theme rounded-pill">
                View All <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <div class="row g-4">
            @foreach($featuredProducts as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 product-card">
                        <a href="{{ route('store.product', ['slug' => $vendor->store_slug, 'productSlug' => $product->slug]) }}" class="text-decoration-none">
                            <div class="position-relative">
                                @if($product->mainPhoto)
                                    <img src="{{ $product->mainPhoto->url }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                @endif
                                @if($product->mrp && $product->selling_price && $product->mrp > $product->selling_price)
                                    @php
                                        $discount = round((($product->mrp - $product->selling_price) / $product->mrp) * 100);
                                    @endphp
                                    <span class="position-absolute top-0 end-0 badge bg-danger m-2 rounded-pill">-{{ $discount }}%</span>
                                @endif
                            </div>
                            <div class="card-body">
                                <h6 class="card-title text-dark fw-bold mb-2">{{ Str::limit($product->name, 40) }}</h6>
                                <div class="d-flex align-items-center gap-2">
                                    @if($product->selling_price)
                                        <span class="fw-bold text-theme">₹{{ number_format($product->selling_price, 2) }}</span>
                                    @endif
                                    @if($product->mrp && $product->mrp > $product->selling_price)
                                        <span class="text-muted text-decoration-line-through small">₹{{ number_format($product->mrp, 2) }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @else
    <!-- No Products Message -->
    <div class="mb-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Products Yet</h4>
                <p class="text-muted mb-0">This store hasn't added any products yet. Check back soon!</p>
            </div>
        </div>
    </div>
    @endif
    
    <!-- About Store Section -->
    @if($vendor->store_description)
    <div class="mb-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">About {{ $vendor->store_name }}</h4>
                <div class="text-muted mb-0">{!! $vendor->store_description !!}</div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Contact Information -->
    <div class="mb-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-4">Contact Information</h4>
                <div class="row g-4">
                    @if($vendor->business_address || $vendor->city || $vendor->state)
                    <div class="col-md-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="bg-theme bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-map-marker-alt text-theme"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fw-bold mb-1">Address</h6>
                                <p class="text-muted mb-0 small">
                                    {{ $vendor->business_address }}<br>
                                    {{ $vendor->city }}{{ $vendor->city && $vendor->state ? ', ' : '' }}{{ $vendor->state }}
                                    {{ $vendor->postal_code ? ' - ' . $vendor->postal_code : '' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($vendor->business_phone)
                    <div class="col-md-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="bg-theme bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-phone text-theme"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fw-bold mb-1">Phone</h6>
                                <a href="tel:{{ $vendor->business_phone }}" class="text-muted text-decoration-none small">{{ $vendor->business_phone }}</a>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($vendor->business_email)
                    <div class="col-md-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="bg-theme bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-envelope text-theme"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fw-bold mb-1">Email</h6>
                                <a href="mailto:{{ $vendor->business_email }}" class="text-muted text-decoration-none small">{{ $vendor->business_email }}</a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-theme {
        color: var(--theme-color);
    }
    
    .bg-theme {
        background-color: var(--theme-color);
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
    
    .category-card {
        transition: all 0.3s ease;
    }
    
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    
    .product-card {
        transition: all 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    
    .bg-opacity-10 {
        background-color: rgba(var(--theme-color-rgb, 0, 123, 255), 0.1) !important;
    }
</style>
@endsection
