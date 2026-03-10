@extends('frontend.layouts.app')

@section('title', 'Home - ' . setting('site_title', 'Frontend App'))

@section('content')
<div class="container-fluid px-0">
    <!-- Default Hero Section with AOS animations -->
    <div class="hero-section text-center py-5 mb-5" style="background: linear-gradient(135deg, var(--theme-color) 0%, var(--link-hover-color) 100%); color: white;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4" style="color: white !important;">
                        Welcome to {{ setting('site_title', 'Frontend App') }}
                    </h1>
                    <p class="lead mb-4" style="color: rgba(255,255,255,0.9) !important;">
                        @auth
                            Welcome back, {{ Auth::user()->name }}! Explore our latest products and categories.
                        @else
                            Discover our amazing products and categories. Join us today!
                        @endauth
                    </p>
                    @auth
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('frontend.profile') }}" class="btn btn-light btn-lg rounded-pill px-4 btn-ripple hover-lift">
                            <i class="fas fa-user me-2"></i>My Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-lg rounded-pill px-4 btn-ripple hover-lift">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('login') }}" class="btn btn-light btn-lg rounded-pill px-4 btn-ripple hover-lift">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="{{ route('frontend.register') }}" class="btn btn-outline-light btn-lg rounded-pill px-4 btn-ripple hover-lift">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </a>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Categories Section -->
    <div class="section mb-5">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0 heading-text" style="color: var(--theme-color);">
                        <i class="fas fa-tags me-2"></i>Categories
                    </h2>
                </div>
                <hr class="my-3">
            </div>
        </div>
        
        @if($categories->count() > 0)
        <div class="row" id="categories-container">
            @foreach($categories as $index => $category)
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4 category-item" @if($index >= 8) style="display: none;" @endif>
                <div class="card h-100 shadow-sm border-0 category-card hover-lift">
                    <div class="position-relative overflow-hidden">
                        @if($category->image_url)
                            <img src="{{ $category->image_url }}" class="card-img-top" alt="{{ $category->name }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-success text-white">{{ $category->product_count }} Products</span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $category->name }}</h5>
                        <p class="card-text flex-grow-1">{{ Str::limit($category->description ?? 'No description available', 100) }}</p>
                        <div class="mt-auto">
                            <small class="text-muted">
                                {{ $category->subCategories->count() }} subcategories • 
                                {{ $category->product_count }} products
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="{{ route('frontend.category.show', $category) }}" class="btn btn-theme w-100 btn-ripple">Explore</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($categories->count() > 8)
        <div class="text-center mt-4" id="categories-buttons-container">
            <button type="button" class="btn btn-outline-theme btn-lg rounded-pill px-5 btn-ripple hover-lift" id="load-more-categories">
                <i class="fas fa-plus-circle me-2"></i>Load More Categories
                <span class="badge bg-theme ms-2" id="categories-remaining">{{ $categories->count() - 8 }}</span>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill px-5 btn-ripple hover-lift ms-2" id="view-less-categories" style="display: none;">
                <i class="fas fa-minus-circle me-2"></i>View Less
            </button>
        </div>
        @endif
        @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>No categories available at the moment.
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Products Section -->
    <div class="section mb-5">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0 heading-text" style="color: var(--theme-color);">
                        <i class="fas fa-box-open me-2"></i>Products
                    </h2>
                </div>
                <hr class="my-3">
            </div>
        </div>
        
        @if($products->count() > 0)
        <div class="row" id="products-container">
            @foreach($products as $index => $product)
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4 product-item" @if($index >= 8) style="display: none;" @endif>
                <div class="card h-100 shadow-sm border-0 product-card hover-lift">
                    <div class="position-relative overflow-hidden">
                        @if($product->vendor)
                            <a href="{{ route('frontend.vendor.product.show', ['vendorSlug' => $product->vendor->store_slug, 'product' => $product->slug]) }}" class="text-decoration-none">
                                @if($product->mainPhoto)
                                    <img src="{{ $product->mainPhoto->url }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                @endif
                            </a>
                        @else
                            <a href="{{ route('frontend.product.show', $product->slug) }}" class="text-decoration-none">
                                @if($product->mainPhoto)
                                    <img src="{{ $product->mainPhoto->url }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                @endif
                            </a>
                        @endif
                        @if($product->variations->count() == 0 && $product->mrp && $product->selling_price && $product->mrp > $product->selling_price)
                            @php
                                $discount = round((($product->mrp - $product->selling_price) / $product->mrp) * 100);
                            @endphp
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-danger">{{ $discount }}% OFF</span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate">
                            @if($product->vendor)
                                <a href="{{ route('frontend.vendor.product.show', ['vendorSlug' => $product->vendor->store_slug, 'product' => $product->slug]) }}" class="text-decoration-none text-dark">
                                    {{ $product->name }}
                                </a>
                            @else
                                <a href="{{ route('frontend.product.show', $product->slug) }}" class="text-decoration-none text-dark">
                                    {{ $product->name }}
                                </a>
                            @endif
                        </h6>
                        
                        <!-- Vendor Badge -->
                        @if($product->vendor)
                            <div class="mb-2">
                                <a href="{{ route('frontend.vendor.store', $product->vendor->store_slug) }}" class="badge bg-light text-dark text-decoration-none">
                                    <i class="fas fa-store me-1"></i>{{ $product->vendor->store_name }}
                                </a>
                            </div>
                        @endif
                        
                        {{-- Only show price for non-variation products --}}
                        @if($product->variations->count() == 0)
                        <div class="d-flex align-items-center gap-2 mt-auto">
                            @if($product->selling_price)
                                <span class="fw-bold text-theme">₹{{ number_format($product->selling_price, 2) }}</span>
                                @if($product->mrp && $product->mrp > $product->selling_price)
                                    <small class="text-muted text-decoration-line-through">₹{{ number_format($product->mrp, 2) }}</small>
                                @endif
                            @else
                                <span class="fw-bold text-theme">₹{{ number_format($product->mrp, 2) }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        @if($product->variations->count() > 0)
                            {{-- Variation product - show View Details button --}}
                            <a href="{{ route('frontend.product.show', $product) }}" class="btn btn-theme w-100 btn-ripple">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        @else
                            {{-- Simple product - show Add to Cart button --}}
                            <button class="btn btn-theme w-100 btn-ripple add-to-cart-btn" 
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->name }}">
                                <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($products->count() > 8)
        <div class="text-center mt-4" id="products-buttons-container">
            <button type="button" class="btn btn-outline-theme btn-lg rounded-pill px-5 btn-ripple hover-lift" id="load-more-products">
                <i class="fas fa-plus-circle me-2"></i>Load More Products
                <span class="badge bg-theme ms-2" id="products-remaining">{{ $products->count() - 8 }}</span>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill px-5 btn-ripple hover-lift ms-2" id="view-less-products" style="display: none;">
                <i class="fas fa-minus-circle me-2"></i>View Less
            </button>
        </div>
        @endif
        @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>No products available at the moment.
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ITEMS_PER_PAGE = 8;
    
    // ========== Categories Load More / View Less ==========
    const loadMoreCategoriesBtn = document.getElementById('load-more-categories');
    const viewLessCategoriesBtn = document.getElementById('view-less-categories');
    const categoriesContainer = document.getElementById('categories-container');
    const categoriesRemainingBadge = document.getElementById('categories-remaining');
    
    if (loadMoreCategoriesBtn && categoriesContainer) {
        let categoriesShown = ITEMS_PER_PAGE;
        const categoryItems = categoriesContainer.querySelectorAll('.category-item');
        const totalCategories = categoryItems.length;
        
        // Load More Categories
        loadMoreCategoriesBtn.addEventListener('click', function() {
            let count = 0;
            for (let i = categoriesShown; i < totalCategories && count < ITEMS_PER_PAGE; i++) {
                categoryItems[i].style.display = '';
                categoryItems[i].style.opacity = '0';
                categoryItems[i].style.transform = 'translateY(20px)';
                setTimeout((function(item) {
                    return function() {
                        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    };
                })(categoryItems[i]), count * 50);
                count++;
            }
            categoriesShown += count;
            
            // Show View Less button
            viewLessCategoriesBtn.style.display = 'inline-block';
            
            // Update remaining count or hide Load More button
            const remaining = totalCategories - categoriesShown;
            if (remaining > 0) {
                categoriesRemainingBadge.textContent = remaining;
            } else {
                loadMoreCategoriesBtn.style.display = 'none';
            }
        });
        
        // View Less Categories
        viewLessCategoriesBtn.addEventListener('click', function() {
            // Hide all items beyond the first 8
            for (let i = ITEMS_PER_PAGE; i < totalCategories; i++) {
                categoryItems[i].style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                categoryItems[i].style.opacity = '0';
                categoryItems[i].style.transform = 'translateY(-10px)';
                setTimeout((function(item) {
                    return function() {
                        item.style.display = 'none';
                    };
                })(categoryItems[i]), 200);
            }
            categoriesShown = ITEMS_PER_PAGE;
            
            // Reset buttons
            loadMoreCategoriesBtn.style.display = 'inline-block';
            viewLessCategoriesBtn.style.display = 'none';
            categoriesRemainingBadge.textContent = totalCategories - ITEMS_PER_PAGE;
            
            // Scroll to categories section
            setTimeout(function() {
                categoriesContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 250);
        });
    }
    
    // ========== Products Load More / View Less ==========
    const loadMoreProductsBtn = document.getElementById('load-more-products');
    const viewLessProductsBtn = document.getElementById('view-less-products');
    const productsContainer = document.getElementById('products-container');
    const productsRemainingBadge = document.getElementById('products-remaining');
    
    if (loadMoreProductsBtn && productsContainer) {
        let productsShown = ITEMS_PER_PAGE;
        const productItems = productsContainer.querySelectorAll('.product-item');
        const totalProducts = productItems.length;
        
        // Load More Products
        loadMoreProductsBtn.addEventListener('click', function() {
            let count = 0;
            for (let i = productsShown; i < totalProducts && count < ITEMS_PER_PAGE; i++) {
                productItems[i].style.display = '';
                productItems[i].style.opacity = '0';
                productItems[i].style.transform = 'translateY(20px)';
                setTimeout((function(item) {
                    return function() {
                        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    };
                })(productItems[i]), count * 50);
                count++;
            }
            productsShown += count;
            
            // Show View Less button
            viewLessProductsBtn.style.display = 'inline-block';
            
            // Update remaining count or hide Load More button
            const remaining = totalProducts - productsShown;
            if (remaining > 0) {
                productsRemainingBadge.textContent = remaining;
            } else {
                loadMoreProductsBtn.style.display = 'none';
            }
        });
        
        // View Less Products
        viewLessProductsBtn.addEventListener('click', function() {
            // Hide all items beyond the first 8
            for (let i = ITEMS_PER_PAGE; i < totalProducts; i++) {
                productItems[i].style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                productItems[i].style.opacity = '0';
                productItems[i].style.transform = 'translateY(-10px)';
                setTimeout((function(item) {
                    return function() {
                        item.style.display = 'none';
                    };
                })(productItems[i]), 200);
            }
            productsShown = ITEMS_PER_PAGE;
            
            // Reset buttons
            loadMoreProductsBtn.style.display = 'inline-block';
            viewLessProductsBtn.style.display = 'none';
            productsRemainingBadge.textContent = totalProducts - ITEMS_PER_PAGE;
            
            // Scroll to products section
            setTimeout(function() {
                productsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 250);
        });
    }
    
    // ========== Add to Cart Functionality ==========
    document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            
            // You can implement AJAX add to cart here
            alert('Added "' + productName + '" to cart!');
        });
    });
});
</script>
@endsection

@push('styles')
<style>
    /* Product card hover effects */
    .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Category card hover effects */
    .category-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Product image link hover effect */
    .product-card .card-img-top {
        transition: transform 0.3s ease;
    }
    
    .product-card:hover .card-img-top {
        transform: scale(1.05);
    }
    
    /* Product title link styling */
    .card-title a {
        color: #333;
        transition: color 0.3s ease;
    }
    
    .card-title a:hover {
        color: var(--theme-color, #007bff);
    }
    
    /* Ensure images don't overflow on hover */
    .position-relative.overflow-hidden {
        overflow: hidden !important;
    }
    
    /* Add cursor pointer to clickable elements */
    .card-title a,
    .product-card .card-img-top {
        cursor: pointer;
    }
</style>
@endpush
