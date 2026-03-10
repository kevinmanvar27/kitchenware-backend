@extends('frontend.layouts.app')

@section('title', 'My Wishlist - ' . setting('site_title', 'Frontend App'))

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Wishlist</li>
        </ol>
    </nav>
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="heading-text mb-0">
                    <i class="fas fa-heart text-danger me-2"></i>My Wishlist
                </h1>
                @auth
                    @if($totalItems > 0)
                    <button type="button" class="btn btn-outline-danger" id="clearWishlistBtn">
                        <i class="fas fa-trash me-2"></i>Clear All
                    </button>
                    @endif
                @endauth
            </div>
            <hr class="my-3">
        </div>
    </div>
    
    @guest
        <!-- Guest User Wishlist -->
        <div class="row" id="guestWishlistContainer">
            <!-- Guest wishlist items will be loaded here via JavaScript -->
        </div>
        
        <!-- Empty State (hidden by default, shown if no items) -->
        <div class="row d-none" id="guestEmptyWishlist">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-heart fa-4x text-muted mb-4"></i>
                        <h3 class="mb-3">Your Wishlist is Empty</h3>
                        <p class="text-muted mb-4">Start adding products to your wishlist! Login to sync your wishlist across devices.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="{{ route('frontend.home') }}" class="btn btn-theme btn-lg">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-theme btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @if($totalItems > 0)
            <!-- Wishlist Items -->
            <div class="row" id="wishlistItemsContainer">
                @foreach($wishlistItems as $item)
                    @php
                        $product = $item->product;
                        if (!$product) continue;
                        
                        $hasSellingPrice = !is_null($product->selling_price) && $product->selling_price !== '';
                        $displayPrice = $hasSellingPrice ? $product->selling_price : $product->mrp;
                        $calculatedPrice = $displayPrice;
                        
                        if (Auth::check() && $hasSellingPrice) {
                            $user = Auth::user();
                            
                            if (!is_null($user->discount_percentage) && $user->discount_percentage > 0) {
                                $calculatedPrice = $product->selling_price * (1 - $user->discount_percentage / 100);
                            } else {
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
                        
                        $displayStock = $product->isVariable() ? $product->total_stock : $product->stock_quantity;
                        $isInStock = $displayStock > 0;
                    @endphp
                    
                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4 wishlist-item" data-product-id="{{ $product->id }}">
                        <div class="card h-100 shadow-sm border-0 wishlist-card hover-lift">
                            <div class="position-relative">
                                @if($product->mainPhoto)
                                    <img src="{{ $product->mainPhoto->url }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                @endif
                                <div class="position-absolute top-0 end-0 m-2">
                                    <button type="button" class="btn btn-danger btn-sm rounded-circle remove-wishlist-btn" data-product-id="{{ $product->id }}" title="Remove from Wishlist">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                @if(!$isInStock)
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-danger">Out of Stock</span>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    @if($item->vendor)
                                        <a href="{{ route('frontend.vendor.product.show', ['vendorSlug' => $item->vendor->store_slug, 'product' => $product->slug]) }}" class="text-decoration-none">
                                            {{ $product->name }}
                                        </a>
                                    @else
                                        <a href="{{ route('frontend.product.show', $product->slug) }}" class="text-decoration-none">
                                            {{ $product->name }}
                                        </a>
                                    @endif
                                </h5>
                                <p class="card-text flex-grow-1 text-muted small">{{ Str::limit($product->description ?? 'No description available', 80) }}</p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <p class="fw-bold text-success mb-0 fs-5">₹{{ number_format($calculatedPrice, 2) }}</p>
                                            @if($hasSellingPrice && $product->mrp > $product->selling_price)
                                                <small class="text-muted text-decoration-line-through">₹{{ number_format($product->mrp, 2) }}</small>
                                            @endif
                                        </div>
                                        @if($isInStock)
                                            <small class="text-success">
                                                <i class="fas fa-check-circle me-1"></i>In Stock
                                            </small>
                                        @endif
                                    </div>
                                    
                                    @if($isInStock)
                                        @if($product->isVariable())
                                            @if($item->vendor)
                                                <a href="{{ route('frontend.vendor.product.show', ['vendorSlug' => $item->vendor->store_slug, 'product' => $product->slug]) }}" class="btn btn-theme w-100 btn-sm">
                                                    <i class="fas fa-eye me-1"></i>View Product
                                                </a>
                                            @else
                                                <a href="{{ route('frontend.product.show', $product->slug) }}" class="btn btn-theme w-100 btn-sm">
                                                    <i class="fas fa-eye me-1"></i>View Product
                                                </a>
                                            @endif
                                        @else
                                            <button type="button" class="btn btn-theme w-100 btn-sm add-to-cart-from-wishlist" data-product-id="{{ $product->id }}">
                                                <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                            </button>
                                        @endif
                                    @else
                                        <button type="button" class="btn btn-secondary w-100 btn-sm" disabled>
                                            <i class="fas fa-exclamation-circle me-1"></i>Out of Stock
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty Wishlist -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-heart fa-4x text-muted mb-4"></i>
                            <h3 class="mb-3">Your Wishlist is Empty</h3>
                            <p class="text-muted mb-4">Start adding products you love to your wishlist!</p>
                            <a href="{{ route('frontend.home') }}" class="btn btn-theme btn-lg">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth
</div>

<style>
    .wishlist-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .wishlist-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }
    
    .remove-wishlist-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .remove-wishlist-btn:hover {
        transform: scale(1.1);
    }
    
    .wishlist-card .card-title a {
        color: var(--theme-color);
        font-weight: 600;
    }
    
    .wishlist-card .card-title a:hover {
        color: var(--link-hover-color);
    }
</style>

@auth
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Remove individual item from wishlist
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-wishlist-btn')) {
                const button = e.target.closest('.remove-wishlist-btn');
                const productId = button.dataset.productId;
                const wishlistItem = button.closest('.wishlist-item');
                
                if (confirm('Are you sure you want to remove this item from your wishlist?')) {
                    button.disabled = true;
                    
                    fetch('/wishlist/remove/' + productId, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            wishlistItem.remove();
                            
                            // Update wishlist count
                            updateWishlistCount(data.data?.total_items || 0);
                            
                            // Check if wishlist is empty
                            const remainingItems = document.querySelectorAll('.wishlist-item');
                            if (remainingItems.length === 0) {
                                location.reload();
                            }
                            
                            showToast(data.message, 'success');
                        } else {
                            showToast(data.message, 'error');
                            button.disabled = false;
                        }
                    })
                    .catch(error => {
                        showToast('An error occurred while removing the item.', 'error');
                        button.disabled = false;
                    });
                }
            }
            
            // Add to cart from wishlist
            if (e.target.closest('.add-to-cart-from-wishlist')) {
                const button = e.target.closest('.add-to-cart-from-wishlist');
                const productId = button.dataset.productId;
                
                button.disabled = true;
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
                
                fetch('{{ route("frontend.cart.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: 1
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount(data.cart_count);
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred while adding to cart.', 'error');
                })
                .finally(() => {
                    button.disabled = false;
                    button.innerHTML = originalText;
                });
            }
        });
        
        // Clear all wishlist items
        const clearWishlistBtn = document.getElementById('clearWishlistBtn');
        if (clearWishlistBtn) {
            clearWishlistBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to clear your entire wishlist?')) {
                    this.disabled = true;
                    
                    fetch('/wishlist/clear', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateWishlistCount(0);
                            showToast(data.message, 'success');
                            location.reload();
                        } else {
                            showToast(data.message, 'error');
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        showToast('An error occurred while clearing the wishlist.', 'error');
                        this.disabled = false;
                    });
                }
            });
        }
    });
</script>
@endauth

@guest
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load guest wishlist from localStorage
        const guestWishlist = getGuestWishlist();
        const container = document.getElementById('guestWishlistContainer');
        const emptyState = document.getElementById('guestEmptyWishlist');
        
        if (guestWishlist.length === 0) {
            emptyState.classList.remove('d-none');
        } else {
            // Fetch product details for wishlist items
            const productIds = guestWishlist.map(item => item.product_id);
            
            fetch('/api/v1/products/by-ids', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ product_ids: productIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    renderGuestWishlist(data.data);
                } else {
                    emptyState.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error loading wishlist:', error);
                emptyState.classList.remove('d-none');
            });
        }
        
        // Function to render guest wishlist items
        function renderGuestWishlist(products) {
            const container = document.getElementById('guestWishlistContainer');
            container.innerHTML = '';
            
            products.forEach(product => {
                const productCard = createProductCard(product);
                container.appendChild(productCard);
            });
        }
        
        // Function to create product card
        function createProductCard(product) {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4 col-xl-3 mb-4 wishlist-item';
            col.dataset.productId = product.id;
            
            const displayPrice = product.selling_price || product.mrp;
            const isInStock = product.stock_quantity > 0;
            
            col.innerHTML = `
                <div class="card h-100 shadow-sm border-0 wishlist-card hover-lift">
                    <div class="position-relative">
                        ${product.main_photo_url ? 
                            `<img src="${product.main_photo_url}" class="card-img-top" alt="${product.name}" style="height: 200px; object-fit: cover;">` :
                            `<div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>`
                        }
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-guest-wishlist-btn" 
                                data-product-id="${product.id}" title="Remove from Wishlist">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2">
                            <a href="/products/${product.slug}" class="text-decoration-none">${product.name}</a>
                        </h5>
                        <p class="card-text text-muted small mb-3">${product.description ? product.description.substring(0, 100) : 'No description available'}...</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="fw-bold text-theme fs-5">₹${parseFloat(displayPrice).toFixed(2)}</div>
                                    ${product.mrp && product.selling_price && product.selling_price < product.mrp ? 
                                        `<small class="text-muted text-decoration-line-through">₹${parseFloat(product.mrp).toFixed(2)}</small>` : 
                                        ''
                                    }
                                </div>
                                <span class="badge ${isInStock ? 'bg-success' : 'bg-danger'}">
                                    ${isInStock ? 'In Stock' : 'Out of Stock'}
                                </span>
                            </div>
                            <button type="button" class="btn btn-theme w-100 add-to-cart-from-guest-wishlist ${!isInStock ? 'disabled' : ''}" 
                                    data-product-id="${product.id}" ${!isInStock ? 'disabled' : ''}>
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            return col;
        }
        
        // Handle remove from guest wishlist
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-guest-wishlist-btn')) {
                const button = e.target.closest('.remove-guest-wishlist-btn');
                const productId = button.dataset.productId;
                const wishlistItem = button.closest('.wishlist-item');
                
                if (confirm('Are you sure you want to remove this item from your wishlist?')) {
                    removeFromGuestWishlist(productId);
                    wishlistItem.remove();
                    updateGuestWishlistCount();
                    showToast('Removed from wishlist', 'success');
                    
                    // Check if wishlist is empty
                    const remainingItems = document.querySelectorAll('.wishlist-item');
                    if (remainingItems.length === 0) {
                        document.getElementById('guestEmptyWishlist').classList.remove('d-none');
                    }
                }
            }
            
            // Handle add to cart from guest wishlist
            if (e.target.closest('.add-to-cart-from-guest-wishlist')) {
                const button = e.target.closest('.add-to-cart-from-guest-wishlist');
                const productId = button.dataset.productId;
                
                button.disabled = true;
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
                
                try {
                    addToGuestCart(productId, 1, null);
                    updateGuestCartCount();
                    showToast('Product added to cart successfully!', 'success');
                } catch (error) {
                    showToast('An error occurred while adding to cart.', 'error');
                } finally {
                    button.disabled = false;
                    button.innerHTML = originalText;
                }
            }
        });
    });
</script>
@endguest
@endsection
