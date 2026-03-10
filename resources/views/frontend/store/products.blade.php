@extends('frontend.layouts.app')

@section('title', $vendor->store_name . ' - Products')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('store.show', $vendor->store_slug) }}" class="text-decoration-none">{{ $vendor->store_name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Products</li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Filters</h5>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <form action="{{ route('store.products', $vendor->store_slug) }}" method="GET" id="filter-form">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Search</label>
                            <input type="text" class="form-control rounded-pill" name="search" value="{{ request('search') }}" placeholder="Search products...">
                        </div>
                        
                        <!-- Categories -->
                        @if($categories->count() > 0)
                        <div class="mb-4">
                            <label class="form-label fw-bold">Categories</label>
                            <div class="list-group list-group-flush">
                                <a href="{{ route('store.products', ['slug' => $vendor->store_slug, 'search' => request('search'), 'sort' => request('sort')]) }}" 
                                   class="list-group-item list-group-item-action border-0 rounded {{ !request('category') ? 'active bg-theme text-white' : '' }}">
                                    All Categories
                                </a>
                                @foreach($categories as $category)
                                    <a href="{{ route('store.products', ['slug' => $vendor->store_slug, 'category' => $category->id, 'search' => request('search'), 'sort' => request('sort')]) }}" 
                                       class="list-group-item list-group-item-action border-0 rounded {{ request('category') == $category->id ? 'active bg-theme text-white' : '' }}">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <!-- Sort -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Sort By</label>
                            <select class="form-select rounded-pill" name="sort" onchange="this.form.submit()">
                                <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name: Z to A</option>
                            </select>
                        </div>
                        
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        
                        <button type="submit" class="btn btn-theme w-100 rounded-pill">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Store Info Card -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body text-center">
                    <img src="{{ $vendor->store_logo_url }}" alt="{{ $vendor->store_name }}" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                    <h6 class="fw-bold mb-2">{{ $vendor->store_name }}</h6>
                    @if($vendor->city || $vendor->state)
                        <p class="text-muted small mb-0">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $vendor->city }}{{ $vendor->city && $vendor->state ? ', ' : '' }}{{ $vendor->state }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">
                    @if(request('category'))
                        @php
                            $currentCategory = $categories->firstWhere('id', request('category'));
                        @endphp
                        {{ $currentCategory ? $currentCategory->name : 'Products' }}
                    @else
                        All Products
                    @endif
                </h4>
                <span class="text-muted">{{ $products->total() }} products found</span>
            </div>
            
            @if($products->count() > 0)
                <div class="row g-4">
                    @foreach($products as $product)
                        <div class="col-6 col-md-4">
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
                                        @if($product->short_description)
                                            <p class="text-muted small mb-2">{{ Str::limit($product->short_description, 60) }}</p>
                                        @endif
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
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-5">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No products found</h5>
                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                    <a href="{{ route('store.products', $vendor->store_slug) }}" class="btn btn-theme rounded-pill px-4">
                        <i class="fas fa-refresh me-2"></i>Clear Filters
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .text-theme {
        color: var(--theme-color);
    }
    
    .bg-theme {
        background-color: var(--theme-color) !important;
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
    
    .product-card {
        transition: all 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    
    .list-group-item.active.bg-theme {
        border-color: var(--theme-color) !important;
    }
</style>
@endsection
