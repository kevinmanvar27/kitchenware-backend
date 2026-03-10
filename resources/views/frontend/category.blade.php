@extends('frontend.layouts.app')

@section('title', $metaTitle ?? 'Category - ' . setting('site_title', 'Frontend App'))
@section('meta_description', $metaDescription ?? setting('tagline', 'Your Frontend Application'))

@section('content')
<div class="container mt-4">
    <div class="row">          
        <!-- Category Header -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        @if(isset($vendor) && $vendor)
                            <li class="breadcrumb-item"><a href="{{ route('frontend.vendor.home', $vendor->store_slug) }}">{{ $vendor->store_name }}</a></li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">Home</a></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="display-5 fw-bold mb-0 category-title">{{ $category->name }}</h1>
                </div>
                @if($category->description)
                    <div class="lead category-description">{!! $category->description !!}</div>
                @endif
            </div>
        </div>
        <!-- Sidebar for Subcategories -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 sticky-top sidebar-card">
                <div class="card-header bg-theme text-white py-3">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-tags me-2 sidebar-icon"></i>
                        <span>{{ $category->name }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($subCategories->count() > 0)
                        <div class="list-group list-group-flush">
                            <!-- All Products Option -->
                            <button type="button" class="list-group-item list-group-item-action active subcategory-filter d-flex justify-content-between align-items-center" data-subcategory-id="">
                                <span>All Products</span>
                                <span class="badge bg-primary rounded-pill badge-count">{{ $products->count() }}</span>
                            </button>
                            
                            <!-- Subcategory Items -->
                            @foreach($subCategories as $index => $subCategory)
                                <button type="button" class="list-group-item list-group-item-action subcategory-filter d-flex justify-content-between align-items-center" data-subcategory-id="{{ $subCategory->id }}">
                                    <span>{{ $subCategory->name }}</span>
                                    @php
                                        $subCategoryProductCount = $products->filter(function ($product) use ($subCategory) {
                                            if (!$product->product_categories) return false;
                                            foreach ($product->product_categories as $catData) {
                                                if (isset($catData['subcategory_ids']) && in_array($subCategory->id, $catData['subcategory_ids'])) {
                                                    return true;
                                                }
                                            }
                                            return false;
                                        })->count();
                                    @endphp
                                    @if($subCategoryProductCount > 0)
                                        <span class="badge bg-secondary rounded-pill badge-count">{{ $subCategoryProductCount }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3 empty-subcategories">
                            <p class="text-muted text-center mb-0">
                                <i class="fas fa-info-circle me-2 pulse-icon"></i>No subcategories available.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Main Content Area for Products -->
        <div class="col-lg-9 col-md-8">
            <div class="section">
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-0 heading-text products-heading">
                            <i class="fas fa-box-open me-2 heading-icon"></i>Products
                        </h2>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="d-flex justify-content-md-end align-items-center sort-container">
                            <span class="me-2 text-muted">Sort by:</span>
                            <select class="form-select form-select-sm w-auto sort-select" id="sort-products">
                                <option value="default" {{ (isset($sort) && $sort == 'default') ? 'selected' : '' }}>Default</option>
                                <option value="name" {{ (isset($sort) && $sort == 'name') ? 'selected' : '' }}>Name</option>
                                <option value="price-low" {{ (isset($sort) && $sort == 'price-low') ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price-high" {{ (isset($sort) && $sort == 'price-high') ? 'selected' : '' }}>Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                </div>
                <hr class="my-3 divider-animated">
                
                <div id="loading-spinner" class="text-center d-none my-5">
                    <div class="spinner-border text-primary spinner-animated" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted loading-text">Loading products...</p>
                </div>
                
                <div id="products-container" class="products-animated">
                    @include('frontend.partials.products-list', ['products' => $products])
                </div>
                
                @if($products->count() > 12)
                    <div class="text-center mt-4">
                        <button class="btn btn-theme load-more-btn" id="load-more-products">
                            <i class="fas fa-sync-alt me-2 load-icon"></i>Load More Products
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --theme-color: {{ setting('theme_color', '#007bff') }};
        --hover-color: {{ setting('link_hover_color', '#0056b3') }};
        --sidebar-active: var(--theme-color);
    }
    
    .category-title { color: var(--theme-color); }
    
    .breadcrumb { background-color: #f8f9fa; padding: 0.75rem 1rem; border-radius: 0.375rem; }
    .breadcrumb:hover { background-color: #e9ecef; }
    .breadcrumb-item a { color: var(--theme-color); }
    .breadcrumb-item a:hover { color: var(--hover-color); text-decoration: underline; }
    
    .sidebar-card { overflow: hidden; }
    .sidebar-card:hover { box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important; }
    .bg-theme { background-color: var(--theme-color) !important; }
    .card.h-100 { min-height: 300px; }
    .sticky-top { top: 100px; }
    
    .list-group-item { border: none; border-radius: 0 !important; padding: 12px 15px; position: relative; overflow: hidden; }
    .list-group-item::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 3px; background-color: var(--theme-color); }
    .list-group-item:first-child { border-top-left-radius: calc(0.375rem - 1px) !important; border-top-right-radius: calc(0.375rem - 1px) !important; }
    .list-group-item:last-child { border-bottom-left-radius: calc(0.375rem - 1px) !important; border-bottom-right-radius: calc(0.375rem - 1px) !important; }
    .list-group-item:hover { background-color: rgba(0, 123, 255, 0.08); padding-left: 20px; }
    .list-group-item.active { background-color: rgba(0, 123, 255, 0.1) !important; border-color: transparent !important; color: var(--sidebar-active) !important; font-weight: 600; }
    .list-group-item.active:hover { background-color: rgba(0, 123, 255, 0.15) !important; }
    
    .list-group-item.active .badge-count { background-color: var(--theme-color) !important; }
    
    .products-heading { color: var(--theme-color); position: relative; }
    
    .sort-select { border-color: #dee2e6; border-radius: 0.375rem; cursor: pointer; }
    .sort-select:hover { border-color: var(--theme-color); box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1); }
    .sort-select:focus { border-color: var(--theme-color); box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25); }
    
    .divider-animated { background: var(--theme-color); height: 2px; border: none; }
    
    #loading-spinner { padding: 2rem; }
    
    .product-card { border-radius: 12px; overflow: hidden; }
    .product-card:hover { box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important; }
    .product-card .card-img-top { height: 200px; object-fit: cover; }
    
    .product-link { color: var(--theme-color); font-weight: 600; position: relative; }
    .product-link:hover { color: var(--hover-color); }
    
    .btn-theme { background-color: var(--theme-color) !important; border-color: var(--theme-color) !important; color: white !important; position: relative; overflow: hidden; }
    .btn-theme:hover { background-color: var(--hover-color) !important; border-color: var(--hover-color) !important; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); }
    
    .btn-outline-theme { border-color: var(--theme-color) !important; color: var(--theme-color) !important; position: relative; overflow: hidden; }
    .btn-outline-theme:hover { background-color: var(--theme-color) !important; border-color: var(--theme-color) !important; color: white !important; }
    
    .load-more-btn { padding: 12px 30px; font-weight: 600; border-radius: 50px; }
    .badge.bg-theme { background-color: var(--theme-color) !important; }
    
    @media (max-width: 768px) {
        .product-card .card-img-top { height: 150px !important; }
        .card.h-100 { min-height: auto; }
        .sticky-top { position: static; }
        .text-md-end { text-align: left !important; }
        .justify-content-md-end { justify-content: flex-start !important; }
        .sort-container { margin-top: 1rem; }
        .category-title { font-size: 1.75rem; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($vendor) && $vendor)
        const baseUrl = "{{ route('frontend.vendor.category.show', ['vendorSlug' => $vendor->store_slug, 'category' => $category->slug]) }}";
    @else
        const baseUrl = "{{ route('frontend.category.show', $category->slug) }}";
    @endif
    

    
    const subcategoryFilterButtons = document.querySelectorAll('.subcategory-filter');
    subcategoryFilterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const subcategoryId = this.getAttribute('data-subcategory-id');
            
            subcategoryFilterButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            const productsContainer = document.getElementById('products-container');
            document.getElementById('loading-spinner').classList.remove('d-none');
            productsContainer.classList.add('d-none');
            
            const currentSort = document.getElementById('sort-products').value;
            let url = `${baseUrl}?subcategory=${subcategoryId}`;
            if (currentSort && currentSort !== 'default') { url += `&sort=${currentSort}`; }
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.text())
            .then(html => {
                productsContainer.innerHTML = html;
                document.getElementById('loading-spinner').classList.add('d-none');
                productsContainer.classList.remove('d-none');
                
                const productCount = document.querySelectorAll('#products-container .col-md-6').length;
                const badge = document.querySelector('.badge.bg-primary');
                if (badge) { badge.textContent = `${productCount} Products`; }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loading-spinner').classList.add('d-none');
                productsContainer.classList.remove('d-none');
                showToast('Failed to load products. Please try again.', 'error');
            });
        });
    });
    
    const sortSelect = document.getElementById('sort-products');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortBy = this.value;
            const subcategoryId = document.querySelector('.subcategory-filter.active')?.getAttribute('data-subcategory-id') || '';
            
            const productsContainer = document.getElementById('products-container');
            document.getElementById('loading-spinner').classList.remove('d-none');
            productsContainer.classList.add('d-none');
            
            let url = `${baseUrl}?sort=${sortBy}`;
            if (subcategoryId) { url += `&subcategory=${subcategoryId}`; }
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.text())
            .then(html => {
                productsContainer.innerHTML = html;
                document.getElementById('loading-spinner').classList.add('d-none');
                productsContainer.classList.remove('d-none');
                
                const productCount = document.querySelectorAll('#products-container .col-md-6').length;
                const badge = document.querySelector('.badge.bg-primary');
                if (badge) { badge.textContent = `${productCount} Products`; }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loading-spinner').classList.add('d-none');
                productsContainer.classList.remove('d-none');
                showToast('Failed to sort products. Please try again.', 'error');
            });
        });
    }
    
    const loadMoreButton = document.getElementById('load-more-products');
    if (loadMoreButton) {
        loadMoreButton.addEventListener('click', function() {
            showToast('Load more functionality would be implemented here', 'info');
        });
    }
    
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0 position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function() {
            document.body.removeChild(toast);
        });
    }
});
</script>
@endsection
