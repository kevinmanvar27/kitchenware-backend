@extends('vendor.layouts.app')

@section('title', 'Add Banner')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Add Banner'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Add New Banner</h4>
                                        <p class="mb-0 text-muted small">Upload a new banner image for your store</p>
                                    </div>
                                    <a href="{{ route('vendor.banners.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                        <i class="fas fa-arrow-left me-1"></i>Back
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <strong>Please fix the following errors:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <form action="{{ route('vendor.banners.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-4">
                                                <label for="title" class="form-label fw-semibold">Banner Title <span class="text-danger">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('title') is-invalid @enderror" 
                                                       id="title" 
                                                       name="title" 
                                                       value="{{ old('title') }}" 
                                                       placeholder="Enter banner title"
                                                       required>
                                                @error('title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">A descriptive title for internal reference</small>
                                            </div>
                                            
                                            <!-- NEW: Link Type Selection -->
                                            <div class="mb-4">
                                                <label class="form-label fw-semibold">Link Type <span class="text-danger">*</span></label>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="link_type" id="link_type_category" value="category" checked>
                                                    <label class="btn btn-outline-primary" for="link_type_category">
                                                        <i class="fas fa-folder me-1"></i> Category
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="link_type" id="link_type_product" value="product">
                                                    <label class="btn btn-outline-primary" for="link_type_product">
                                                        <i class="fas fa-box me-1"></i> Product
                                                    </label>
                                                </div>
                                                <small class="text-muted">Choose where this banner should redirect users</small>
                                            </div>

                                            <!-- NEW: Category Selection -->
                                            <div class="mb-4" id="category_selection" style="display: block;">
                                                <label for="category_id" class="form-label fw-semibold">Select Category <span class="text-danger">*</span></label>
                                                <select class="form-select" id="category_id" name="category_id">
                                                    <option value="">-- Loading categories... --</option>
                                                </select>
                                                <small class="text-muted">Choose a category to link this banner to</small>
                                            </div>

                                            <!-- NEW: Product Selection -->
                                            <div class="mb-4" id="product_selection" style="display: none;">
                                                <label for="product_id" class="form-label fw-semibold">Select Product <span class="text-danger">*</span></label>
                                                <select class="form-select" id="product_id" name="product_id">
                                                    <option value="">-- Loading products... --</option>
                                                </select>
                                                <small class="text-muted">Choose a product to link this banner to</small>
                                            </div>
                                            
                                            <!-- Modified: Redirect URL (auto-generated) -->
                                            <div class="mb-4">
                                                <label for="redirect_url" class="form-label fw-semibold">Redirect URL <span class="text-danger">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('redirect_url') is-invalid @enderror" 
                                                       id="redirect_url" 
                                                       name="redirect_url" 
                                                       value="{{ old('redirect_url') }}" 
                                                       placeholder="Select a category or product to generate URL"
                                                       required
                                                       readonly>
                                                @error('redirect_url')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted" id="url_hint">URL will be auto-generated based on category or product selection</small>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="image" class="form-label fw-semibold">Banner Image <span class="text-danger">*</span></label>
                                                <input type="file" 
                                                       class="form-control @error('image') is-invalid @enderror" 
                                                       id="image" 
                                                       name="image" 
                                                       accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                                       required
                                                       onchange="previewImage(event)">
                                                @error('image')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Accepted formats: JPEG, PNG, GIF, WEBP. Max size: 2MB. Recommended dimensions: 1920x600px</small>
                                            </div>
                                            
                                            <!-- Image Preview -->
                                            <div class="mb-4" id="image-preview-container" style="display: none;">
                                                <label class="form-label fw-semibold">Image Preview</label>
                                                <div class="border rounded p-2 bg-light">
                                                    <img id="image-preview" src="" alt="Preview" class="img-fluid rounded" style="max-height: 300px;">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="card border-0 shadow-sm bg-light">
                                                <div class="card-body">
                                                    <h6 class="fw-bold mb-3">Banner Settings</h6>
                                                    
                                                    <div class="mb-3">
                                                        <label for="display_order" class="form-label fw-semibold">Display Order</label>
                                                        <input type="number" 
                                                               class="form-control @error('display_order') is-invalid @enderror" 
                                                               id="display_order" 
                                                               name="display_order" 
                                                               value="{{ old('display_order', 0) }}" 
                                                               min="0">
                                                        @error('display_order')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <small class="text-muted">Lower numbers appear first</small>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" 
                                                                   type="checkbox" 
                                                                   id="is_active" 
                                                                   name="is_active" 
                                                                   value="1" 
                                                                   {{ old('is_active', true) ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-semibold" for="is_active">
                                                                Active Status
                                                            </label>
                                                        </div>
                                                        <small class="text-muted">Enable to show banner on your store</small>
                                                    </div>
                                                    
                                                    <hr class="my-3">
                                                    
                                                    <div class="alert alert-info mb-0 small">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        <strong>Tip:</strong> Use high-quality images for better visual appeal. Banners are great for promotions and announcements.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                        <a href="{{ route('vendor.banners.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Create Banner
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Store categories and products data
    let categoriesData = [];
    let productsData = [];

    // Fetch categories on page load
    async function fetchCategories() {
        try {
            console.log('Fetching categories from:', "{{ route('vendor.banners.api.categories') }}");
            const response = await fetch("{{ route('vendor.banners.api.categories') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            console.log('Categories response status:', response.status);
            const result = await response.json();
            console.log('Categories result:', result);
            
            if (result.success) {
                categoriesData = result.data;
                console.log('Categories loaded:', categoriesData.length);
                populateCategoryDropdown();
            } else {
                console.error('Categories API returned error:', result.message);
                document.getElementById('category_id').innerHTML = '<option value="">Error: ' + result.message + '</option>';
            }
        } catch (error) {
            console.error('Error fetching categories:', error);
            document.getElementById('category_id').innerHTML = '<option value="">Error loading categories</option>';
        }
    }

    // Fetch products on page load
    async function fetchProducts() {
        try {
            console.log('Fetching products from:', "{{ route('vendor.banners.api.products') }}");
            const response = await fetch("{{ route('vendor.banners.api.products') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            console.log('Products response status:', response.status);
            const result = await response.json();
            console.log('Products result:', result);
            
            if (result.success) {
                productsData = result.data;
                console.log('Products loaded:', productsData.length);
                populateProductDropdown();
            } else {
                console.error('Products API returned error:', result.message);
                document.getElementById('product_id').innerHTML = '<option value="">Error: ' + result.message + '</option>';
            }
        } catch (error) {
            console.error('Error fetching products:', error);
            document.getElementById('product_id').innerHTML = '<option value="">Error loading products</option>';
        }
    }

    // Populate category dropdown
    function populateCategoryDropdown() {
        const select = document.getElementById('category_id');
        console.log('Populating category dropdown, categories count:', categoriesData.length);
        select.innerHTML = '<option value="">-- Select a category --</option>';
        
        categoriesData.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            option.dataset.slug = category.slug;
            select.appendChild(option);
        });
        console.log('Category dropdown populated with', select.options.length - 1, 'categories');
    }

    // Populate product dropdown
    function populateProductDropdown() {
        const select = document.getElementById('product_id');
        console.log('Populating product dropdown, products count:', productsData.length);
        select.innerHTML = '<option value="">-- Select a product --</option>';
        
        productsData.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = product.name;
            select.appendChild(option);
        });
        console.log('Product dropdown populated with', select.options.length - 1, 'products');
    }

    // Handle link type change
    document.querySelectorAll('input[name="link_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const linkType = this.value;
            const categorySection = document.getElementById('category_selection');
            const productSection = document.getElementById('product_selection');
            const urlInput = document.getElementById('redirect_url');
            const urlHint = document.getElementById('url_hint');
            
            // Reset selections
            document.getElementById('category_id').value = '';
            document.getElementById('product_id').value = '';
            urlInput.value = '';
            
            if (linkType === 'category') {
                categorySection.style.display = 'block';
                productSection.style.display = 'none';
                urlHint.textContent = 'URL will be auto-generated based on category selection';
            } else if (linkType === 'product') {
                categorySection.style.display = 'none';
                productSection.style.display = 'block';
                urlHint.textContent = 'URL will be auto-generated based on product selection';
            }
        });
    });

    // Handle category selection
    document.getElementById('category_id').addEventListener('change', function() {
        const categoryId = this.value;
        
        if (categoryId) {
            const url = `/customer/categories/${categoryId}/subcategories`;
            document.getElementById('redirect_url').value = url;
        } else {
            document.getElementById('redirect_url').value = '';
        }
    });

    // Handle product selection
    document.getElementById('product_id').addEventListener('change', function() {
        const productId = this.value;
        
        if (productId) {
            const url = `/customer/products/${productId}`;
            document.getElementById('redirect_url').value = url;
        } else {
            document.getElementById('redirect_url').value = '';
        }
    });

    // Image preview function
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('image-preview-container').style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        fetchCategories();
        fetchProducts();
    });
</script>
@endpush
