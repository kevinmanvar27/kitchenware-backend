@extends('frontend.layouts.app')

@section('title', $metaTitle ?? 'Product - ' . setting('site_title', 'Frontend App'))
@section('meta_description', $metaDescription ?? setting('tagline', 'Your Frontend Application'))

@section('content')
<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" class="my-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">Home</a></li>
            @if(isset($vendor) && $vendor)
                <li class="breadcrumb-item"><a href="{{ route('frontend.vendor.store', $vendor->store_slug) }}">{{ $vendor->store_name }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Product Gallery -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 product-gallery-card hover-lift">
                <div class="card-body">
                    <!-- Main Product Image -->
                    <div class="main-image-container mb-3">
                        @if($product->mainPhoto)
                            <img id="main-image" src="{{ $product->mainPhoto->url }}" class="img-fluid rounded main-product-image" alt="{{ $product->name }}" style="width: 100%; height: 400px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 100%; height: 400px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                        
                        <!-- Image Zoom Overlay -->
                        <div class="image-zoom-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    
                    <!-- Thumbnail Gallery -->
                    <div class="thumbnail-gallery d-flex flex-wrap gap-2" id="thumbnail-gallery">
                        <!-- Main Photo Thumbnail -->
                        @if($product->mainPhoto)
                        <div class="thumbnail-item active" data-image="{{ $product->mainPhoto->url }}" data-variation-id="">
                            <img src="{{ $product->mainPhoto->url }}" class="img-thumbnail" alt="{{ $product->name }}" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                        @endif
                        
                        <!-- Gallery Thumbnails -->
                        @foreach($product->gallery_photos as $index => $media)
                        <div class="thumbnail-item" data-image="{{ $media['url'] }}" data-variation-id="">
                            <img src="{{ $media['url'] }}" class="img-thumbnail" alt="{{ $product->name }}" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                        @endforeach
                        
                        <!-- Variation Images (for variable products) -->
                        @if($product->isVariable() && $product->variations->count() > 0)
                            @foreach($product->variations as $variation)
                                @if($variation->image)
                                <div class="thumbnail-item variation-thumbnail" 
                                     data-image="{{ $variation->image_url }}" 
                                     data-variation-id="{{ $variation->id }}"
                                     style="display: none;">
                                    <img src="{{ $variation->image_url }}" class="img-thumbnail" alt="{{ $variation->display_name }}" style="width: 80px; height: 80px; object-fit: cover;">
                                    <div class="variation-label">{{ implode(', ', array_values($variation->formatted_attributes)) }}</div>
                                </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 product-details-card hover-lift">
                <div class="card-body">
                    <!-- Product Name -->
                    <h1 class="heading-text mb-3 product-title">{{ $product->name }}</h1>
                    
                    <!-- Product Status -->
                    <div class="mb-3">
                        <span class="badge status-badge {{ $product->status === 'active' || $product->status === 'published' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </div>
                    
                    <!-- Variable Product - Variation Selection -->
                    @if($product->isVariable() && $product->variations->count() > 0)
                    <div class="variation-selection mb-4">
                        <h5 class="mb-3">Select Options:</h5>
                        @php
                            $attributeGroups = [];
                            foreach($product->variations as $variation) {
                                foreach($variation->attribute_values as $attrId => $valueId) {
                                    if (!isset($attributeGroups[$attrId])) {
                                        $attributeGroups[$attrId] = [
                                            'values' => [],
                                            'name' => ''
                                        ];
                                    }
                                    if (!in_array($valueId, $attributeGroups[$attrId]['values'])) {
                                        $attributeGroups[$attrId]['values'][] = $valueId;
                                    }
                                }
                            }
                        @endphp
                        
                        @foreach($attributeGroups as $attrId => $attrData)
                            @php
                                $attribute = \App\Models\ProductAttribute::with('values')->find($attrId);
                            @endphp
                            @if($attribute)
                            <div class="mb-4 variation-group">
                                <label class="form-label fw-bold text-uppercase mb-3" style="font-size: 0.875rem; letter-spacing: 0.5px; color: #495057;">
                                    <i class="fas fa-tag me-2" style="color: var(--theme-color);"></i>{{ $attribute->name }}
                                </label>
                                <div class="btn-group-toggle d-flex flex-wrap gap-2" data-toggle="buttons">
                                    @foreach($attribute->values->whereIn('id', $attrData['values']) as $value)
                                    <label class="btn btn-outline-theme variation-option px-4 py-2" data-attribute-id="{{ $attrId }}" data-value-id="{{ $value->id }}" style="border-width: 2px; border-radius: 8px; font-weight: 500; transition: all 0.3s ease;">
                                        <input type="radio" name="attribute_{{ $attrId }}" value="{{ $value->id }}" autocomplete="off">
                                        {{ $value->value }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                        
                        <input type="hidden" id="selected-variation-id" name="variation_id" value="">
                    </div>
                    @endif
                    
                    <!-- Pricing Information -->
                    <div class="pricing-section mb-4">
                        @php
                            // For variable products, we'll update price dynamically via JS
                            // For simple products, use existing logic
                            if ($product->isSimple()) {
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
                            } else {
                                // Variable product - show price range
                                $priceRange = $product->price_range;
                                $calculatedPrice = $priceRange['min'];
                                $hasSellingPrice = true;
                            }
                        @endphp
                        
                        <div class="price-display">
                            @if($product->isVariable())
                                <span class="fw-bold text-success h4 current-price" id="variation-price">
                                    ₹{{ number_format($product->price_range['min'], 2) }} - ₹{{ number_format($product->price_range['max'], 2) }}
                                </span>
                            @else
                                <span class="fw-bold text-success h4 current-price">₹{{ number_format($calculatedPrice, 2) }}</span>
                                @if($hasSellingPrice && $product->mrp > $product->selling_price)
                                    <span class="text-muted text-decoration-line-through ms-2 original-price">₹{{ number_format($product->mrp, 2) }}</span>
                                    @php
                                        $discountPercentage = round((($product->mrp - $product->selling_price) / $product->mrp) * 100);
                                    @endphp
                                    <span class="badge bg-danger ms-2 discount-badge">{{ $discountPercentage }}% OFF</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    <!-- Availability Status -->
                    <div class="availability-section mb-4" id="stock-status-container">
                        @if($product->isVariable())
                            <div class="d-flex align-items-center stock-status" id="variation-stock">
                                <i class="fas fa-info-circle text-muted me-2 stock-icon"></i>
                                <span class="text-muted">Please select options to see availability</span>
                            </div>
                        @else
                            @if($product->in_stock)
                                <div class="d-flex align-items-center stock-status in-stock">
                                    <i class="fas fa-check-circle text-success me-2 stock-icon"></i>
                                    <span class="fw-bold text-success">In Stock</span>
                                    <span class="ms-2 stock-quantity">({{ $product->stock_quantity }} available)</span>
                                </div>
                            @else
                                <div class="d-flex align-items-center stock-status out-of-stock">
                                    <i class="fas fa-times-circle text-danger me-2 stock-icon"></i>
                                    <span class="fw-bold text-danger">Out of Stock</span>
                                </div>
                            @endif
                        @endif
                    </div>
                    
                    <!-- Quantity Input -->
                    <div class="quantity-section mb-4">
                        <label class="form-label fw-bold">Quantity:</label>
                        <div class="input-group quantity-control" style="max-width: 150px;">
                            <button class="btn btn-outline-theme decrement-qty" type="button" id="decrement-qty">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="form-control text-center" id="product-quantity" value="1" min="1" max="9999">
                            <button class="btn btn-outline-theme increment-qty" type="button" id="increment-qty">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <small class="text-muted" id="quantity-error" style="display: none; color: #dc3545 !important;">
                            <i class="fas fa-exclamation-circle"></i> Quantity must be at least 1
                        </small>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons mb-4">
                        @if($product->isVariable())
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-theme btn-lg buy-now-btn btn-ripple hover-lift" data-product-id="{{ $product->id }}" disabled id="buy-now-btn">
                                    <i class="fas fa-bolt me-2"></i>Buy Now
                                </button>
                                <button type="button" class="btn btn-outline-theme btn-lg add-to-cart-btn btn-ripple hover-lift" data-product-id="{{ $product->id }}" disabled id="add-to-cart-btn">
                                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-lg wishlist-btn btn-ripple hover-lift" data-product-id="{{ $product->id }}" id="wishlist-btn">
                                    <i class="far fa-heart me-2"></i><span class="wishlist-text">Add to Wishlist</span>
                                </button>
                            </div>
                        @else
                            @if($product->in_stock)
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-theme btn-lg buy-now-btn btn-ripple hover-lift" data-product-id="{{ $product->id }}" id="buy-now-btn">
                                        <i class="fas fa-bolt me-2"></i>Buy Now
                                    </button>
                                    <button type="button" class="btn btn-outline-theme btn-lg add-to-cart-btn btn-ripple hover-lift" data-product-id="{{ $product->id }}" id="add-to-cart-btn">
                                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-lg wishlist-btn btn-ripple hover-lift" data-product-id="{{ $product->id }}" id="wishlist-btn">
                                        <i class="far fa-heart me-2"></i><span class="wishlist-text">Add to Wishlist</span>
                                    </button>
                                </div>
                            @else
                                <button type="button" class="btn btn-secondary btn-lg" disabled>
                                    <i class="fas fa-exclamation-circle me-2"></i>Out of Stock
                                </button>
                            @endif
                        @endif
                    </div>
                    
                    <!-- Product Meta Information -->
                    <div class="product-meta">
                        @if(isset($vendor) && $vendor)
                        <!-- Vendor/Seller Information -->
                        <div class="vendor-info mb-4 p-3 bg-light rounded-3">
                            <div class="d-flex align-items-center">
                                <div class="vendor-logo me-3">
                                    @if($vendor->store_logo_url)
                                        <img src="{{ $vendor->store_logo_url }}" alt="{{ $vendor->store_name }}" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-theme d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <span class="text-white fw-bold">{{ strtoupper(substr($vendor->store_name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="vendor-details flex-grow-1">
                                    <p class="mb-0 fw-bold">Sold by</p>
                                    <a href="{{ route('frontend.vendor.store', $vendor->store_slug) }}" class="text-theme text-decoration-none fw-bold">
                                        {{ $vendor->store_name }}
                                        <i class="fas fa-external-link-alt ms-1 small"></i>
                                    </a>
                                </div>
                                <a href="{{ route('frontend.vendor.store', $vendor->store_slug) }}" class="btn btn-outline-theme btn-sm rounded-pill">
                                    <i class="fas fa-store me-1"></i>Visit Store
                                </a>
                            </div>
                        </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-sm-6">
                                <p class="mb-1 meta-item"><strong>SKU:</strong> <span class="meta-value" id="product-sku">{{ $product->id }}</span></p>
                                <p class="mb-1 meta-item"><strong>Status:</strong> <span class="meta-value">{{ ucfirst($product->status) }}</span></p>
                            </div>
                            <div class="col-sm-6">
                                @if($product->created_at)
                                    <p class="mb-1 meta-item"><strong>Added:</strong> <span class="meta-value">{{ $product->created_at->format('M d, Y') }}</span></p>
                                @endif
                                @if($product->updated_at)
                                    <p class="mb-1 meta-item"><strong>Updated:</strong> <span class="meta-value">{{ $product->updated_at->format('M d, Y') }}</span></p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Description -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0 description-card hover-lift">
                <div class="card-body">
                    <h2 class="heading-text mb-3"><i class="fas fa-align-left me-2"></i>Product Description</h2>
                    <div class="general-text description-content">
                        @if($product->description)
                            <div>{!! $product->description !!}</div>
                        @else
                            <p class="text-muted">No description available for this product.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Product Gallery Styles */
    .product-gallery-card {
        overflow: hidden;
    }
    
    .main-image-container {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
    }
    
    .main-product-image {
    }
    
    .image-zoom-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0,0,0,0.5);
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        font-size: 1.5rem;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }
    
    .main-image-container:hover .image-zoom-overlay {
        opacity: 1;
    }
    
    /* Thumbnail Styles */
    .thumbnail-item {
        cursor: pointer;
        border: 2px solid transparent;
        opacity: 1;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .thumbnail-item:hover {
        border-color: var(--theme-color);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .thumbnail-item.active {
        border-color: var(--theme-color);
        box-shadow: 0 0 0 3px rgba(var(--theme-color-rgb), 0.25);
    }
    
    .variation-label {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.7);
        color: white;
        font-size: 10px;
        padding: 2px 4px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Product Title */
    .product-title {
    }
    
    /* Status Badge */
    .status-badge {
    }
    
    /* Price */
    .current-price {
        display: inline-block;
    }
    
    .discount-badge {
    }
    
    .original-price {
        position: relative;
        text-decoration: line-through;
    }
    
    /* Stock Status */
    .stock-status {
        padding: 10px 15px;
        border-radius: 8px;
    }
    
    .stock-status.in-stock {
        background: rgba(40, 167, 69, 0.1);
    }
    
    .stock-status.out-of-stock {
        background: rgba(220, 53, 69, 0.1);
    }
    
    .stock-icon {
    }
    
    .stock-quantity {
        opacity: 1;
    }
    
    /* Quantity Control */
    .quantity-control {
        display: inline-flex;
    }
    
    .quantity-control .btn {
        padding: 0.5rem 1rem;
    }
    
    .quantity-control input {
        max-width: 80px;
        text-align: center;
    }
    
    .quantity-control input::-webkit-inner-spin-button,
    .quantity-control input::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    /* Action Buttons */
    .action-buttons .btn {
        position: relative;
        overflow: hidden;
    }
    
    .action-buttons .btn i {
    }
    
    .buy-now-btn:hover {
    }
    
    /* Meta Information */
    .meta-item {
        padding: 8px;
        border-radius: 5px;
    }
    
    .meta-item:hover {
        background-color: rgba(var(--theme-color-rgb), 0.05);
    }
    
    .meta-value {
        color: var(--theme-color);
    }
    
    /* Description Card */
    .description-card {
        position: relative;
        overflow: hidden;
    }
    
    .description-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, var(--theme-color), var(--link-hover-color));
    }
    
    .description-content {
    }
    
    /* Variation Selection Styles */
    .variation-group {
        background: #f8f9fa;
        padding: 1.25rem;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .variation-group:hover {
        border-color: var(--theme-color);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .variation-option {
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .variation-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    
    .variation-option:hover {
        background-color: var(--theme-color);
        color: white !important;
        border-color: var(--theme-color) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .variation-option.active {
        background-color: var(--theme-color) !important;
        color: white !important;
        border-color: var(--theme-color) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        transform: scale(1.05);
    }
    
    .variation-option.active::after {
        content: '\f00c';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 4px;
        right: 8px;
        font-size: 0.75rem;
        color: white;
    }
    
    .variation-selection {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1rem;
        background-color: #f8f9fa;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .main-image-container img {
            height: 300px !important;
        }
        
        .thumbnail-item img {
            width: 60px !important;
            height: 60px !important;
        }
        
        .price-display {
            font-size: 1.25rem;
        }
    }
</style>

<!-- JavaScript for Image Gallery and Product Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== Quantity Controls ==========
    const quantityInput = document.getElementById('product-quantity');
    const incrementBtn = document.getElementById('increment-qty');
    const decrementBtn = document.getElementById('decrement-qty');
    const quantityError = document.getElementById('quantity-error');
    
    // Validate quantity
    function validateQuantity() {
        let qty = parseInt(quantityInput.value) || 0;
        
        if (qty < 1) {
            quantityInput.value = 1;
            quantityError.style.display = 'block';
            return false;
        } else {
            quantityError.style.display = 'none';
            return true;
        }
    }
    
    // Increment quantity
    if (incrementBtn) {
        incrementBtn.addEventListener('click', function() {
            let currentQty = parseInt(quantityInput.value) || 1;
            quantityInput.value = currentQty + 1;
            validateQuantity();
        });
    }
    
    // Decrement quantity
    if (decrementBtn) {
        decrementBtn.addEventListener('click', function() {
            let currentQty = parseInt(quantityInput.value) || 1;
            if (currentQty > 1) {
                quantityInput.value = currentQty - 1;
            }
            validateQuantity();
        });
    }
    
    // Validate on input change
    if (quantityInput) {
        quantityInput.addEventListener('change', validateQuantity);
        quantityInput.addEventListener('blur', validateQuantity);
    }
    
    // ========== Image Gallery ==========
    const thumbnailItems = document.querySelectorAll('.thumbnail-item');
    const mainImage = document.getElementById('main-image');
    
    if (thumbnailItems.length > 0 && mainImage) {
        thumbnailItems.forEach((item, index) => {
            item.addEventListener('click', function() {
                // Remove active class from all thumbnails
                thumbnailItems.forEach(thumb => thumb.classList.remove('active'));
                
                // Add active class to clicked thumbnail
                this.classList.add('active');
                
                // Update main image
                const imageUrl = this.getAttribute('data-image');
                mainImage.src = imageUrl;
                mainImage.alt = "{{ $product->name }}";
            });
        });
    }
    
    // Image zoom on click
    const mainImageContainer = document.querySelector('.main-image-container');
    if (mainImageContainer && mainImage) {
        mainImageContainer.addEventListener('click', function() {
            // Create lightbox
            const lightbox = document.createElement('div');
            lightbox.className = 'lightbox-overlay';
            lightbox.innerHTML = `
                <div class="lightbox-content">
                    <img src="${mainImage.src}" alt="${mainImage.alt}">
                    <button class="lightbox-close">&times;</button>
                </div>
            `;
            document.body.appendChild(lightbox);
            document.body.style.overflow = 'hidden';
            
            // Animate in
            setTimeout(() => lightbox.classList.add('active'), 10);
            
            // Close on click
            lightbox.addEventListener('click', function(e) {
                if (e.target === lightbox || e.target.classList.contains('lightbox-close')) {
                    lightbox.classList.remove('active');
                    setTimeout(() => {
                        document.body.removeChild(lightbox);
                        document.body.style.overflow = '';
                    }, 300);
                }
            });
        });
    }
    
    @if($product->isVariable())
    @php
        $variationsData = $product->variations->map(function($v) {
            return [
                'id' => $v->id,
                'attribute_values' => $v->attribute_values,
                'mrp' => $v->mrp,
                'selling_price' => $v->selling_price,
                'stock_quantity' => $v->stock_quantity,
                'in_stock' => $v->in_stock,
                'display_name' => $v->display_name,
                'sku' => $v->sku,
                'image_url' => $v->image_url
            ];
        });
    @endphp
    
    // Variable Product - Variation Selection
    const variations = @json($variationsData);
    
    let selectedAttributes = {};
    const variationOptions = document.querySelectorAll('.variation-option');
    
    variationOptions.forEach(option => {
        option.addEventListener('click', function() {
            const attrId = this.getAttribute('data-attribute-id');
            const valueId = this.getAttribute('data-value-id');
            
            // Remove active from siblings
            const siblings = document.querySelectorAll(`.variation-option[data-attribute-id="${attrId}"]`);
            siblings.forEach(s => s.classList.remove('active'));
            
            // Add active to this
            this.classList.add('active');
            
            // Store selection
            selectedAttributes[attrId] = valueId;
            
            // Find matching variation
            findMatchingVariation();
        });
    });
    
    function findMatchingVariation() {
        // Check if all attributes are selected
        if (variations.length === 0) return;
        
        const requiredAttributes = Object.keys(variations[0].attribute_values);
        const selectedKeys = Object.keys(selectedAttributes);
        
        if (requiredAttributes.length !== selectedKeys.length) {
            return; // Not all attributes selected yet
        }
        
        // Find matching variation
        const matchingVariation = variations.find(v => {
            return Object.keys(v.attribute_values).every(attrId => {
                return v.attribute_values[attrId] == selectedAttributes[attrId];
            });
        });
        
        if (matchingVariation) {
            updateProductDisplay(matchingVariation);
        }
    }
    
    function updateProductDisplay(variation) {
        // Update hidden input
        const variationIdInput = document.getElementById('selected-variation-id');
        if (variationIdInput) {
            variationIdInput.value = variation.id;
        }
        
        // Update price
        const priceDisplay = document.getElementById('variation-price');
        if (priceDisplay) {
            const price = variation.selling_price || variation.mrp;
            priceDisplay.innerHTML = `₹${parseFloat(price).toFixed(2)}`;
        }
        
        // Update SKU
        const skuDisplay = document.getElementById('product-sku');
        if (skuDisplay && variation.sku) {
            skuDisplay.textContent = variation.sku;
        }
        
        // Update main image if variation has an image
        if (variation.image_url && mainImage) {
            mainImage.src = variation.image_url;
            
            // Show variation thumbnail and hide others
            const allThumbnails = document.querySelectorAll('.thumbnail-item');
            allThumbnails.forEach(thumb => {
                thumb.classList.remove('active');
                const thumbVariationId = thumb.getAttribute('data-variation-id');
                
                // Show variation thumbnails, hide main product thumbnails
                if (thumbVariationId === '') {
                    // Main product images - hide them
                    thumb.style.display = 'none';
                } else if (thumbVariationId === variation.id.toString()) {
                    // This variation's image - show and activate
                    thumb.style.display = 'block';
                    thumb.classList.add('active');
                } else {
                    // Other variations - show but not active
                    thumb.style.display = 'block';
                }
            });
        }
        
        // Update stock status
        const stockContainer = document.getElementById('variation-stock');
        if (stockContainer) {
            // Check stock quantity only - status field is for admin control, not inventory
            if (variation.stock_quantity > 0) {
                stockContainer.innerHTML = `
                    <i class="fas fa-check-circle text-success me-2 stock-icon"></i>
                    <span class="fw-bold text-success">In Stock</span>
                    <span class="ms-2 stock-quantity">(${variation.stock_quantity} available)</span>
                `;
                
                // Enable buttons
                const buyNowBtn = document.getElementById('buy-now-btn');
                const addToCartBtn = document.getElementById('add-to-cart-btn');
                if (buyNowBtn) buyNowBtn.disabled = false;
                if (addToCartBtn) addToCartBtn.disabled = false;
            } else {
                stockContainer.innerHTML = `
                    <i class="fas fa-times-circle text-danger me-2 stock-icon"></i>
                    <span class="fw-bold text-danger">Out of Stock</span>
                `;
                
                // Disable buttons
                const buyNowBtn = document.getElementById('buy-now-btn');
                const addToCartBtn = document.getElementById('add-to-cart-btn');
                if (buyNowBtn) buyNowBtn.disabled = true;
                if (addToCartBtn) addToCartBtn.disabled = true;
            }
        }
    }
    
    // Update add to cart functionality for variable products
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    const buyNowBtn = document.getElementById('buy-now-btn');
    
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            // Validate quantity first
            if (!validateQuantity()) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            const variationIdInput = document.getElementById('selected-variation-id');
            const variationId = variationIdInput ? variationIdInput.value : '';
            const quantity = parseInt(quantityInput.value) || 1;
            
            if (!variationId) {
                e.preventDefault();
                e.stopPropagation();
                alert('Please select all product options first.');
                return false;
            }
            
            // The existing add to cart handler will pick up the variation_id
            this.setAttribute('data-variation-id', variationId);
            this.setAttribute('data-quantity', quantity);
        });
    }
    
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function(e) {
            // Validate quantity first
            if (!validateQuantity()) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            const variationIdInput = document.getElementById('selected-variation-id');
            const variationId = variationIdInput ? variationIdInput.value : '';
            const quantity = parseInt(quantityInput.value) || 1;
            
            if (!variationId) {
                e.preventDefault();
                e.stopPropagation();
                alert('Please select all product options first.');
                return false;
            }
            
            // The existing buy now handler will pick up the variation_id
            this.setAttribute('data-variation-id', variationId);
            this.setAttribute('data-quantity', quantity);
        });
    }
    @else
    // Simple product - just validate quantity
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    const buyNowBtn = document.getElementById('buy-now-btn');
    
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            if (!validateQuantity()) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            const quantity = parseInt(quantityInput.value) || 1;
            this.setAttribute('data-quantity', quantity);
        });
    }
    
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function(e) {
            if (!validateQuantity()) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            const quantity = parseInt(quantityInput.value) || 1;
            this.setAttribute('data-quantity', quantity);
        });
    }
    @endif
});

// Initialize wishlist button state on page load
document.addEventListener('DOMContentLoaded', function() {
    const wishlistBtn = document.getElementById('wishlist-btn');
    if (!wishlistBtn) return;
    
    const productId = wishlistBtn.dataset.productId;
    
    @auth
        // Check if product is in wishlist - use web route for authenticated users
        fetch('/wishlist/check/' + productId, {
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.is_in_wishlist) {
                wishlistBtn.classList.add('active');
                const icon = wishlistBtn.querySelector('i');
                const text = wishlistBtn.querySelector('.wishlist-text');
                if (icon) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                }
                if (text) {
                    text.textContent = 'Remove from Wishlist';
                }
            }
        })
        .catch(error => {
            console.error('Error checking wishlist status:', error);
        });
    @else
        // Guest user - check localStorage
        if (isInGuestWishlist(productId)) {
            wishlistBtn.classList.add('active');
            const icon = wishlistBtn.querySelector('i');
            const text = wishlistBtn.querySelector('.wishlist-text');
            if (icon) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            }
            if (text) {
                text.textContent = 'Remove from Wishlist';
            }
        }
    @endauth
});
</script>

<style>
    /* Lightbox Styles */
    .lightbox-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .lightbox-overlay.active {
        opacity: 1;
    }
    
    .lightbox-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
    }
    
    .lightbox-overlay.active .lightbox-content {
    }
    
    .lightbox-content img {
        max-width: 100%;
        max-height: 90vh;
        object-fit: contain;
        border-radius: 8px;
    }
    
    .lightbox-close {
        position: absolute;
        top: -40px;
        right: 0;
        background: none;
        border: none;
        color: white;
        font-size: 2rem;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .lightbox-close:hover {
        transform: scale(1.2);
    }
</style>
@endsection
