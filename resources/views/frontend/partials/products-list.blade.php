@if($products->count() > 0)
<div class="row">
    @foreach($products as $index => $product)
    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
        <div class="card h-100 shadow-sm border-0 product-card">
            <div class="position-relative product-image-container">
                @if($product->mainPhoto)
                    <img src="{{ $product->mainPhoto->url }}" class="card-img-top product-image" alt="{{ $product->name }}" loading="lazy">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center product-placeholder" style="height: 200px;">
                        <i class="fas fa-image fa-3x text-muted placeholder-icon"></i>
                    </div>
                @endif
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge bg-success text-white status-badge">{{ ucfirst($product->status) }}</span>
                </div>
                <div class="product-overlay">
                    @if(isset($vendor) && $vendor)
                        <a href="{{ route('frontend.vendor.product.show', ['vendorSlug' => $vendor->store_slug, 'product' => $product->slug]) }}" class="btn btn-light btn-sm quick-view-btn">
                            <i class="fas fa-eye me-1"></i>Quick View
                        </a>
                    @else
                        <a href="{{ route('frontend.product.show', $product->slug) }}" class="btn btn-light btn-sm quick-view-btn">
                            <i class="fas fa-eye me-1"></i>Quick View
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body d-flex flex-column">
                <h5 class="card-title product-title">
                    @if(isset($vendor) && $vendor)
                        <a href="{{ route('frontend.vendor.product.show', ['vendorSlug' => $vendor->store_slug, 'product' => $product->slug]) }}" class="product-link text-decoration-none">
                            {{ $product->name }}
                        </a>
                    @else
                        <a href="{{ route('frontend.product.show', $product->slug) }}" class="product-link text-decoration-none">
                            {{ $product->name }}
                        </a>
                    @endif
                </h5>
                <p class="card-text flex-grow-1 product-description">{{ Str::limit($product->description ?? 'No description available', 100) }}</p>
                <div class="mt-auto">
                    <div class="d-flex justify-content-between align-items-center mb-2 price-container">
                        @php
                            $hasSellingPrice = !is_null($product->selling_price) && $product->selling_price !== '';
                            $displayPrice = $hasSellingPrice ? $product->selling_price : $product->mrp;
                            $calculatedPrice = $displayPrice;
                            
                            if (Auth::check() && $hasSellingPrice) {
                                $user = Auth::user();
                                
                                if (!is_null($user->discount_percentage) && $user->discount_percentage > 0) {
                                    $calculatedPrice = $product->selling_price * (1 - $user->discount_percentage / 100);
                                } 
                                else {
                                    $userGroups = $user->userGroups;
                                    if ($userGroups->count() > 0) {
                                        $highestGroupDiscount = 0;
                                        foreach ($userGroups as $group) {
                                            if (!is_null($group->discount_percentage) && $group->discount_percentage > $highestGroupDiscount) {
                                                $highestGroupDiscount = $group->discount_percentage;
                                            }
                                        }
                                        
                                        if ($highestGroupDiscount > 0) {
                                            $calculatedPrice = $product->selling_price * (1 - $highestGroupDiscount / 100);
                                        }
                                    }
                                }
                            }
                        @endphp
                        <p class="fw-bold text-success mb-0 fs-5 product-price">₹{{ number_format($calculatedPrice, 2) }}</p>
                        @if($hasSellingPrice && $product->mrp > $product->selling_price)
                            <small class="text-muted text-decoration-line-through original-price">₹{{ number_format($product->mrp, 2) }}</small>
                        @endif
                    </div>
                    <div class="mb-2 stock-status">
                        <small class="text-muted">
                            @php
                                // For variable products, show total stock from all variations
                                $displayStock = $product->isVariable() ? $product->total_stock : $product->stock_quantity;
                                $isInStock = $displayStock > 0;
                            @endphp
                            @if($isInStock)
                                <i class="fas fa-check-circle text-success me-1 stock-icon"></i>In Stock ({{ $displayStock }})
                            @else
                                <i class="fas fa-times-circle text-danger me-1 stock-icon"></i>Out of Stock
                            @endif
                        </small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                @if($product->isVariable())
                    {{-- Variable Product: Show only View Product button --}}
                    @if(isset($vendor) && $vendor)
                        <a href="{{ route('frontend.vendor.product.show', ['vendorSlug' => $vendor->store_slug, 'product' => $product->slug]) }}" class="btn btn-theme w-100 action-btn">
                            <i class="fas fa-eye me-1 btn-icon"></i>View Product
                        </a>
                    @else
                        <a href="{{ route('frontend.product.show', $product->slug) }}" class="btn btn-theme w-100 action-btn">
                            <i class="fas fa-eye me-1 btn-icon"></i>View Product
                        </a>
                    @endif
                @else
                    {{-- Simple Product: Show Buy Now and Add to Cart buttons --}}
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-theme buy-now-btn action-btn w-100" data-product-id="{{ $product->id }}">
                            <i class="fas fa-bolt me-1 btn-icon"></i>Buy Now
                        </button>
                        <button type="button" class="btn btn-outline-theme add-to-cart-btn action-btn w-100" data-product-id="{{ $product->id }}">
                            <i class="fas fa-shopping-cart me-1 btn-icon"></i>Add to Cart
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
    /* Product Card Styles */
    .product-card {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .product-card:hover {
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Image container */
    .product-image-container {
        overflow: hidden;
        position: relative;
    }
    
    .product-image {
        height: 200px;
        object-fit: cover;
    }
    
    .product-card:hover .product-image {
    }
    
    .product-placeholder {
    }
    
    .placeholder-icon {
    }
    
    .product-card:hover .placeholder-icon {
    }
    
    /* Overlay */
    .product-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
    }
    
    .product-card:hover .product-overlay {
        opacity: 1;
    }
    
    .quick-view-btn {
    }
    
    .product-card:hover .quick-view-btn {
    }
    
    .quick-view-btn:hover {
    }
    
    /* Status badge */
    .status-badge {
    }
    
    .product-card:hover .status-badge {
    }
    
    /* Product title */
    .product-title {
    }
    
    .product-link {
        color: var(--theme-color, #007bff);
        font-weight: 600;
        position: relative;
    }
    
    .product-link::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background-color: var(--hover-color, #0056b3);
    }
    
    .product-link:hover {
        color: var(--hover-color, #0056b3);
    }
    
    .product-link:hover::after {
        width: 100%;
    }
    
    /* Description */
    .product-description {
        color: #6c757d;
        font-size: 0.9rem;
        line-height: 1.5;
    }
    
    /* Price */
    .price-container {
    }
    
    .product-price {
    }
    
    .product-card:hover .product-price {
    }
    
    .original-price {
    }
    
    .product-card:hover .original-price {
        color: #dc3545 !important;
    }
    
    /* Stock status */
    .stock-status {
    }
    
    .stock-icon {
    }
    
    .product-card:hover .stock-icon {
    }
    
    /* Action buttons */
    .action-btn {
        position: relative;
        overflow: hidden;
    }
    
    .action-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
    }
    
    .action-btn:hover::before {
    }
    
    .action-btn:hover {
    }
    
    .action-btn:active {
    }
    
    .btn-icon {
    }
    
    .action-btn:hover .btn-icon {
    }
    
    .buy-now-btn:hover .btn-icon {
    }
    
    .add-to-cart-btn:hover .btn-icon {
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .product-image {
            height: 150px !important;
        }
        
        .product-card:hover {
        }
        
        .product-overlay {
            display: none;
        }
    }
</style>
@else
<div class="row">
    <div class="col-12">
        <div class="alert alert-info text-center py-5 empty-state">
            <i class="fas fa-info-circle fa-2x mb-3 empty-icon"></i>
            <h4 class="alert-heading">No Products Found</h4>
            <p class="mb-0">There are currently no products available in this category. Please check back later or explore other categories.</p>
        </div>
    </div>
</div>

<style>
    .empty-state {
        border-radius: 12px;
        background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
        border: none;
    }
    
    .empty-icon {
        color: #17a2b8;
    }
</style>

@endif
