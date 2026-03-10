@extends('admin.layouts.app')

@section('title', 'Create Product')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Create Product'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Create New Product</h4>
                                    <p class="mb-0 text-muted">Add a new product to the store</p>
                                </div>
                                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Products
                                </a>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if($errors->any())
                                    <div class="alert alert-danger rounded-pill px-4 py-3">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
                                    @csrf
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-4">
                                                <label for="name" class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4 py-2" id="name" name="name" value="{{ old('name') }}" required>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="description" class="form-label fw-bold">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="5" placeholder="Product description...">{{ old('description') }}</textarea>
                                            </div>
                                            
                                            <!-- Product Type Selection -->
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Product Type <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="product_type" id="product_type_simple" value="simple" {{ old('product_type', 'simple') == 'simple' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="product_type_simple">
                                                            <strong>Simple Product</strong>
                                                            <small class="d-block text-muted">A standalone product with no variations</small>
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="product_type" id="product_type_variable" value="variable" {{ old('product_type') == 'variable' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="product_type_variable">
                                                            <strong>Variable Product</strong>
                                                            <small class="d-block text-muted">A product with variations (e.g., Size, Color)</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Simple Product Fields -->
                                            <div id="simple-product-fields" class="{{ old('product_type', 'simple') == 'simple' ? '' : 'd-none' }}">
                                                <div class="row">
                                                    <div class="col-md-6 mb-4">
                                                        <label for="mrp" class="form-label fw-bold">MRP (₹) <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="mrp" name="mrp" value="{{ old('mrp') }}" step="0.01" min="0">
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        <label for="selling_price" class="form-label fw-bold">Selling Price (₹)</label>
                                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="selling_price" name="selling_price" value="{{ old('selling_price') }}" step="0.01" min="0">
                                                        <div class="form-text">Must be less than or equal to MRP</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Variable Product Fields -->
                                            <div id="variable-product-fields" class="{{ old('product_type') == 'variable' ? '' : 'd-none' }}">
                                                <!-- Attribute Selection -->
                                                <div class="mb-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="form-label fw-bold mb-0">Product Attributes <span class="text-danger">*</span></label>
                                                        <button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#attributeModal">
                                                            <i class="fas fa-plus me-1"></i> Add New Attribute
                                                        </button>
                                                    </div>
                                                    <p class="text-muted small">Select attributes for this variable product (e.g., Size, Color)</p>
                                                    <div id="attribute-selection" class="border rounded-3 p-3">
                                                        @if(isset($attributes) && $attributes->count() > 0)
                                                            @foreach($attributes as $attribute)
                                                                <div class="form-check mb-2 attribute-item" data-attribute-id="{{ $attribute->id }}">
                                                                    <input class="form-check-input attribute-checkbox" type="checkbox" 
                                                                           id="attribute_{{ $attribute->id }}" 
                                                                           value="{{ $attribute->id }}" 
                                                                           data-attribute-name="{{ $attribute->name }}"
                                                                           data-attribute-values='@json($attribute->values->pluck('value', 'id'))'>
                                                                    <label class="form-check-label" for="attribute_{{ $attribute->id }}">
                                                                        <strong>{{ $attribute->name }}</strong>
                                                                        <small class="text-muted d-block">
                                                                            Values: {{ $attribute->values->pluck('value')->join(', ') }}
                                                                        </small>
                                                                    </label>
                                                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 ms-2 edit-attribute-btn" 
                                                                            data-attribute-id="{{ $attribute->id }}"
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#attributeModal">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <p class="text-muted mb-0" id="no-attributes-message">No attributes available. Click "Add New Attribute" to create one.</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <!-- Variations Accordion -->
                                                <div class="mb-4" id="variations-container" style="display: none;">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <label class="form-label fw-bold mb-0">Product Variations</label>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-sm btn-primary rounded-pill me-2" id="generate-variations-btn">
                                                                <i class="fas fa-magic me-1"></i> Auto-Generate All
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="add-variation-manually-btn">
                                                                <i class="fas fa-plus me-1"></i> Add Manual
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="accordion" id="variationsAccordion">
                                                        <!-- Variation accordion items will be added here dynamically -->
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Stock Status - Only for Simple Products -->
                                            <div class="mb-4" id="simple-stock-fields">
                                                <label class="form-label fw-bold">Stock Status</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input type="hidden" name="in_stock" value="0">
                                                    <input class="form-check-input" type="checkbox" id="in_stock" name="in_stock" value="1" {{ old('in_stock', true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="in_stock">
                                                        <span id="stock-status-text">In Stock</span>
                                                    </label>
                                                </div>
                                                <div id="stock_quantity_container" class="{{ old('in_stock', true) ? '' : 'd-none' }}">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                                            <input type="number" class="form-control rounded-pill px-4 py-2" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="low_quantity_threshold" class="form-label">Low Stock Alert Threshold</label>
                                                            <input type="number" class="form-control rounded-pill px-4 py-2" id="low_quantity_threshold" name="low_quantity_threshold" value="{{ old('low_quantity_threshold', 10) }}" min="0">
                                                            <div class="form-text text-muted">
                                                                <i class="fas fa-bell text-warning me-1"></i>
                                                                You'll receive a notification when stock falls below this quantity
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="status" class="form-label fw-bold">Product Status <span class="text-danger">*</span></label>
                                                <select class="form-select rounded-pill px-4 py-2" id="status" name="status" required>
                                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Category Selection -->
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Categories</label>
                                                <div id="category-selection" class="border rounded-3 p-3" style="max-height: 400px; overflow-y: auto; background-color: #f8f9fa;">
                                                    @if(isset($categories) && $categories->count() > 0)
                                                        @foreach($categories as $category)
                                                            <div class="form-check mb-2 category-item" data-category-id="{{ $category->id }}">
                                                                <input class="form-check-input category-checkbox" type="checkbox" id="category_{{ $category->id }}" value="{{ $category->id }}" name="product_categories[{{ $category->id }}][category_id]">
                                                                <label class="form-check-label fw-bold" for="category_{{ $category->id }}">
                                                                    {{ $category->name }}
                                                                </label>
                                                                @if($category->subCategories->count() > 0)
                                                                    <div class="subcategory-container ms-4 mt-2 d-none" id="subcategory_container_{{ $category->id }}">
                                                                        @foreach($category->subCategories as $subcategory)
                                                                            <div class="form-check mb-1">
                                                                                <input class="form-check-input subcategory-checkbox" type="checkbox" id="subcategory_{{ $subcategory->id }}" value="{{ $subcategory->id }}" name="product_categories[{{ $category->id }}][subcategory_ids][]" data-category-id="{{ $category->id }}">
                                                                                <label class="form-check-label" for="subcategory_{{ $subcategory->id }}">
                                                                                    {{ $subcategory->name }}
                                                                                </label>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <p class="text-muted">No categories available. Please create categories first.</p>
                                                    @endif
                                                </div>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" id="manage-categories-btn">
                                                        <i class="fas fa-plus me-1"></i> Manage Categories & Subcategories
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Main Photo</label>
                                                <input type="file" id="main_photo" name="main_photo" class="d-none" accept="image/*">
                                                <div class="border rounded-3 p-3 text-center" id="main-photo-preview">
                                                    <div class="upload-area" id="main-photo-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                                        <div>
                                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                            <p class="text-muted mb-2">Click to select an image</p>
                                                            <label for="main_photo" class="btn btn-outline-primary btn-sm rounded-pill">
                                                                <i class="fas fa-upload me-1"></i> Upload Image
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Gallery Photos</label>
                                                <div class="border rounded-3 p-3" id="gallery-photos-container">
                                                    <div class="mb-3">
                                                        <label for="gallery_images" class="btn btn-outline-primary btn-sm rounded-pill">
                                                            <i class="fas fa-plus me-1"></i> Add Photos
                                                        </label>
                                                        <input type="file" id="gallery_images" name="gallery_images[]" class="d-none" accept="image/*" multiple>
                                                    </div>
                                                    <div id="gallery-preview" class="d-flex flex-wrap gap-2 mb-3"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="border-top pt-4 mt-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0 fw-bold">SEO Settings</h5>
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" id="toggle-seo-settings">
                                                <i class="fas fa-chevron-down me-1"></i> Expand
                                            </button>
                                        </div>
                                        
                                        <div id="seo-settings-content" class="d-none">
                                            <div class="mb-4">
                                                <label for="meta_title" class="form-label fw-bold">Meta Title</label>
                                                <input type="text" class="form-control rounded-pill px-4 py-2" id="meta_title" name="meta_title" value="{{ old('meta_title') }}" maxlength="255">
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="meta_description" class="form-label fw-bold">Meta Description</label>
                                                <textarea class="form-control" id="meta_description" name="meta_description" rows="3" maxlength="500" placeholder="Brief description for search engines...">{{ old('meta_description') }}</textarea>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="meta_keywords" class="form-label fw-bold">Meta Keywords</label>
                                                <input type="text" class="form-control rounded-pill px-4 py-2" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="keyword1, keyword2, keyword3">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                                            <i class="fas fa-times me-2"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4 py-2">
                                            <i class="fas fa-save me-2"></i> Save Product
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unified Category Management Modal -->
            <div class="modal fade" id="categoryManagementModal" tabindex="-1" aria-labelledby="categoryManagementModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="categoryManagementModalLabel">Manage Categories & Subcategories</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3">Categories</h6>
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <input type="text" class="form-control rounded-pill" id="new-category-name" placeholder="Enter category name">
                                            <button class="btn btn-outline-primary rounded-pill" type="button" id="add-category-btn">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="categories-list" class="border rounded-3 p-3" style="max-height: 300px; overflow-y: auto;">
                                        <!-- Categories will be loaded here dynamically -->
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3">Subcategories</h6>
                                    <div class="mb-3">
                                        <select class="form-select rounded-pill mb-2" id="subcategory-parent-category">
                                            <option value="">Select a category first</option>
                                            @if(isset($categories))
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="input-group">
                                            <input type="text" class="form-control rounded-pill" id="new-subcategory-name" placeholder="Enter subcategory name">
                                            <button class="btn btn-outline-primary rounded-pill" type="button" id="add-subcategory-btn" disabled>
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="subcategories-list" class="border rounded-3 p-3" style="max-height: 300px; overflow-y: auto;">
                                        <!-- Subcategories will be loaded here dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Close
                            </button>
                            <button type="button" class="btn btn-theme rounded-pill" id="save-category-selections">
                                <i class="fas fa-save me-2"></i>Save Selections
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Image Upload Handling
    $(document).ready(function() {
        // Main Photo Upload
        $('#main_photo').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update only the display area inside preview, keep the input intact
                    $('#main-photo-upload-area').replaceWith(`
                        <div class="position-relative" id="main-photo-upload-area">
                            <img src="${e.target.result}" class="img-fluid mb-2" alt="Main Photo" style="max-height: 200px; object-fit: contain;">
                            <div class="mt-2">
                                <label for="main_photo" class="btn btn-outline-primary btn-sm rounded-pill">
                                    <i class="fas fa-upload me-1"></i> Change Image
                                </label>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill ms-2" id="remove-main-photo">
                                    <i class="fas fa-trash me-1"></i> Remove
                                </button>
                            </div>
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Remove Main Photo
        $(document).on('click', '#remove-main-photo', function() {
            $('#main_photo').val('');
            $('#main-photo-upload-area').replaceWith(`
                <div class="upload-area" id="main-photo-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <div>
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-2">Click to select an image</p>
                        <label for="main_photo" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="fas fa-upload me-1"></i> Upload Image
                        </label>
                    </div>
                </div>
            `);
        });
        
        // Gallery Images Upload
        $('#gallery_images').on('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                Array.from(files).forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const newIndex = Date.now() + index;
                        $('#gallery-preview').append(`
                            <div class="position-relative gallery-item" data-index="${newIndex}">
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 80px; width: 80px;">
                                    <img src="${e.target.result}" class="img-fluid" alt="Gallery Image" style="max-height: 100%; max-width: 100%; object-fit: cover;">
                                </div>
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle p-1 remove-gallery-item" data-index="${newIndex}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
        
        // Remove Gallery Item
        $(document).on('click', '.remove-gallery-item', function() {
            $(this).closest('.gallery-item').remove();
        });
    });
    
    // All JavaScript functionality has been moved to resources/js/common.js
    
    // Variable Product Functionality
    let selectedAttributes = [];
    let variationCounter = 0;
    
    $(document).ready(function() {
        // Product Type Toggle
        $('input[name="product_type"]').on('change', function() {
            const productType = $(this).val();
            
            if (productType === 'simple') {
                $('#simple-product-fields').removeClass('d-none');
                $('#simple-stock-fields').removeClass('d-none');
                $('#variable-product-fields').addClass('d-none');
                
                // Make simple product fields required
                $('#mrp, #selling_price').prop('required', true);
            } else {
                $('#simple-product-fields').addClass('d-none');
                $('#simple-stock-fields').addClass('d-none');
                $('#variable-product-fields').removeClass('d-none');
                
                // Remove required from simple product fields
                $('#mrp, #selling_price').prop('required', false);
            }
        });
        
        // Attribute Selection
        $(document).on('change', '.attribute-checkbox', function() {
            updateSelectedAttributes();
            updateVariationsContainer();
        });
        
        function updateSelectedAttributes() {
            selectedAttributes = [];
            $('.attribute-checkbox:checked').each(function() {
                const attributeId = $(this).val();
                const attributeName = $(this).data('attribute-name');
                const attributeValues = $(this).data('attribute-values');
                
                console.log('Selected Attribute:', {
                    id: attributeId,
                    name: attributeName,
                    values: attributeValues
                });
                
                selectedAttributes.push({
                    id: attributeId,
                    name: attributeName,
                    values: attributeValues
                });
            });
            
            console.log('All Selected Attributes:', selectedAttributes);
        }
        
        function updateVariationsContainer() {
            if (selectedAttributes.length > 0) {
                $('#variations-container').show();
            } else {
                $('#variations-container').hide();
                $('#variationsAccordion').empty();
                variationCounter = 0;
            }
        }
        
        // Generate All Variations
        $('#generate-variations-btn').on('click', function() {
            if (selectedAttributes.length === 0) {
                alert('Please select at least one attribute first.');
                return;
            }
            
            console.log('Generating variations for:', selectedAttributes);
            
            // Confirm if variations already exist
            if ($('#variationsAccordion .accordion-item').length > 0) {
                if (!confirm('This will replace all existing variations. Continue?')) {
                    return;
                }
            }
            
            // Generate all combinations
            const combinations = generateCombinations(selectedAttributes);
            
            console.log('Generated combinations:', combinations);
            
            // Clear existing variations
            $('#variationsAccordion').empty();
            variationCounter = 0;
            
            // Add each combination as a row
            let addedCount = 0;
            combinations.forEach(combination => {
                const beforeCount = $('#variationsAccordion .accordion-item').length;
                addVariationRow(combination);
                const afterCount = $('#variationsAccordion .accordion-item').length;
                if (afterCount > beforeCount) {
                    addedCount++;
                }
            });
            
            if (addedCount > 0) {
                alert(`Successfully generated ${addedCount} variation(s)!`);
            } else if (combinations.length > 0) {
                alert('All variations already exist. No new variations were added.');
            }
        });
        
        function generateCombinations(attributes) {
            if (attributes.length === 0) return [{}];
            
            const [first, ...rest] = attributes;
            const restCombinations = generateCombinations(rest);
            const combinations = [];
            
            // Convert values object to array of entries
            const valueEntries = Object.entries(first.values);
            
            console.log(`Processing attribute: ${first.name}, values:`, valueEntries);
            
            valueEntries.forEach(([valueId, valueName]) => {
                restCombinations.forEach(restCombo => {
                    combinations.push({
                        ...restCombo,
                        [first.id]: { id: valueId, name: valueName, attributeName: first.name }
                    });
                });
            });
            
            return combinations;
        }
        
        function addVariationRow(combination = {}, skipDuplicateCheck = false) {
            // Check for duplicates if combination is provided
            if (!skipDuplicateCheck && Object.keys(combination).length > 0) {
                const attributeValues = {};
                Object.entries(combination).forEach(([attrId, attrData]) => {
                    attributeValues[attrId] = attrData.id;
                });
                
                if (isDuplicateVariation(attributeValues)) {
                    console.log('Skipping duplicate variation:', combination);
                    return;
                }
            }
            
            const index = variationCounter++;
            
            // Build variation name
            let variationName = '';
            let attributeInputs = '';
            const combinationEntries = Object.entries(combination);
            
            console.log('Adding variation row for combination:', combination);
            
            combinationEntries.forEach(([attrId, attrData], idx) => {
                if (idx > 0) variationName += ' - ';
                variationName += attrData.name;
                
                attributeInputs += `
                    <input type="hidden" name="variations[${index}][attribute_values][${attrId}]" value="${attrData.id}">
                `;
            });
            
            if (!variationName) {
                variationName = 'Variation ' + (index + 1);
            }
            
            console.log('Variation name:', variationName);
            
            // Build attribute badges for accordion header
            let attributeBadges = '';
            combinationEntries.forEach(([attrId, attrData]) => {
                attributeBadges += `<span class="badge bg-light text-dark border me-1">${attrData.attributeName}: ${attrData.name}</span>`;
            });
            
            const card = `
                <div class="accordion-item variation-card" data-variation-index="${index}">
                    <h2 class="accordion-header" id="heading-${index}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${index}" aria-expanded="false" aria-controls="collapse-${index}">
                            <div class="d-flex align-items-center w-100 me-3">
                                <div class="variation-header-image me-3">
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="text-primary">${variationName}</strong>
                                    <div class="small text-muted">
                                        ${attributeBadges || 'New variation'}
                                    </div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-${index}" class="accordion-collapse collapse" aria-labelledby="heading-${index}" data-bs-parent="#variationsAccordion">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <!-- Variation Image -->
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small">Image</label>
                                    <div class="variation-image-upload">
                                        <div class="image-preview-container position-relative" style="width: 100%; height: 120px; border: 2px dashed #dee2e6; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                                <div class="text-center">
                                                    <i class="fas fa-image fa-2x mb-2"></i>
                                                    <div class="small">No Image</div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="file" 
                                               class="form-control form-control-sm mt-2 variation-image-input" 
                                               name="variations[${index}][image]" 
                                               accept="image/*"
                                               data-variation-index="${index}">
                                    </div>
                                </div>
                                
                                <!-- Variation Details -->
                                <div class="col-md-10">
                                    <div class="row g-3">
                                        <!-- Hidden Inputs for Attributes -->
                                        <div class="col-12">
                                            ${attributeInputs}
                                        </div>
                                    
                                    <!-- SKU -->
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">SKU</label>
                                        <input type="text" class="form-control form-control-sm" 
                                               name="variations[${index}][sku]" 
                                               placeholder="Enter SKU">
                                    </div>
                                    
                                    <!-- MRP -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">MRP (₹)</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][mrp]" 
                                               placeholder="MRP" 
                                               step="0.01" min="0">
                                    </div>
                                    
                                    <!-- Selling Price -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Selling Price (₹)</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][selling_price]" 
                                               placeholder="Price" 
                                               step="0.01" min="0">
                                    </div>
                                    
                                    <!-- Stock Quantity -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Stock <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][stock_quantity]" 
                                               value="0" 
                                               placeholder="Stock" 
                                               min="0"
                                               required>
                                    </div>
                                    
                                    <!-- Low Stock Threshold -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Low Stock Alert</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][low_quantity_threshold]" 
                                               value="10" 
                                               placeholder="Threshold" 
                                               min="0">
                                    </div>
                                    
                                    <!-- Status -->
                                    <div class="col-md-1">
                                        <label class="form-label small fw-bold">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="variations[${index}][in_stock]" 
                                                   value="1" checked>
                                        </div>
                                    </div>
                                    
                                    <!-- Remove Button -->
                                    <div class="col-12">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-variation-btn">
                                            <i class="fas fa-trash me-1"></i> Remove This Variation
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#variationsAccordion').append(card);
        }
        
        // Check for duplicate variation
        function isDuplicateVariation(attributeValues) {
            let isDuplicate = false;
            $('#variationsAccordion .variation-card').each(function() {
                const card = $(this);
                let matches = true;
                
                // Check if all attribute values match
                for (const [attrId, valueId] of Object.entries(attributeValues)) {
                    const existingValue = card.find(`input[name*="[attribute_values][${attrId}]"]`).val();
                    if (existingValue != valueId) {
                        matches = false;
                        break;
                    }
                }
                
                if (matches) {
                    isDuplicate = true;
                    return false; // break the loop
                }
            });
            
            return isDuplicate;
        }
        
        // Add Variation Manually
        $('#add-variation-manually-btn').on('click', function() {
            if (selectedAttributes.length === 0) {
                alert('Please select at least one attribute first.');
                return;
            }
            
            // Build a manual variation selector
            const index = variationCounter++;
            let attributeSelectors = '';
            
            selectedAttributes.forEach(attr => {
                const options = Object.entries(attr.values).map(([id, name]) => 
                    `<option value="${id}">${name}</option>`
                ).join('');
                
                attributeSelectors += `
                    <div class="mb-2">
                        <label class="form-label small">${attr.name}</label>
                        <select class="form-select form-select-sm variation-attribute-select" 
                                data-attribute-id="${attr.id}"
                                data-attribute-name="${attr.name}"
                                name="variations[${index}][attribute_values][${attr.id}]" required>
                            <option value="">Select ${attr.name}</option>
                            ${options}
                        </select>
                    </div>
                `;
            });
            
            const card = `
                <div class="variation-card card border mb-3" data-variation-index="${index}">
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Variation Image -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">Image</label>
                                <div class="variation-image-upload">
                                    <div class="image-preview-container position-relative" style="width: 100%; height: 120px; border: 2px dashed #dee2e6; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                            <div class="text-center">
                                                <i class="fas fa-image fa-2x mb-2"></i>
                                                <div class="small">No Image</div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="file" 
                                           class="form-control form-control-sm mt-2 variation-image-input" 
                                           name="variations[${index}][image]" 
                                           accept="image/*"
                                           data-variation-index="${index}">
                                </div>
                            </div>
                            
                            <!-- Variation Details -->
                            <div class="col-md-10">
                                <div class="row g-3">
                                    <!-- Variation Name -->
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 fw-bold text-primary variation-name-display">Select attributes</h6>
                                                <div class="variation-attributes mt-2">
                                                    ${attributeSelectors}
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-variation-btn" title="Remove Variation">
                                                <i class="fas fa-trash me-1"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- SKU -->
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">SKU</label>
                                        <input type="text" class="form-control form-control-sm" 
                                               name="variations[${index}][sku]" 
                                               placeholder="Enter SKU">
                                    </div>
                                    
                                    <!-- MRP -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">MRP (₹)</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][mrp]" 
                                               placeholder="MRP" 
                                               step="0.01" min="0">
                                    </div>
                                    
                                    <!-- Selling Price -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Selling Price (₹)</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][selling_price]" 
                                               placeholder="Price" 
                                               step="0.01" min="0">
                                    </div>
                                    
                                    <!-- Stock Quantity -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Stock <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][stock_quantity]" 
                                               value="0" 
                                               placeholder="Stock" 
                                               min="0"
                                               required>
                                    </div>
                                    
                                    <!-- Low Stock Threshold -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Low Stock Alert</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][low_quantity_threshold]" 
                                               value="10" 
                                               placeholder="Threshold" 
                                               min="0">
                                    </div>
                                    
                                    <!-- Status -->
                                    <div class="col-md-1">
                                        <label class="form-label small fw-bold">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="variations[${index}][in_stock]" 
                                                   value="1" checked>
                                        </div>
                                    </div>
                                    
                                    <!-- Remove Button -->
                                    <div class="col-12">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-variation-btn">
                                            <i class="fas fa-trash me-1"></i> Remove This Variation
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#variationsAccordion').append(card);
        });
        
        // Handle manual variation attribute selection change
        $(document).on('change', '.variation-attribute-select', function() {
            const card = $(this).closest('.variation-card');
            const nameDisplay = card.find('.variation-name-display');
            
            // Build variation name from selected attributes
            let variationName = '';
            const attributeValues = {};
            let allSelected = true;
            
            card.find('.variation-attribute-select').each(function() {
                const select = $(this);
                const attrId = select.data('attribute-id');
                const attrName = select.data('attribute-name');
                const selectedOption = select.find('option:selected');
                const valueId = select.val();
                const valueName = selectedOption.text();
                
                if (valueId) {
                    if (variationName) variationName += ' - ';
                    variationName += valueName;
                    attributeValues[attrId] = valueId;
                } else {
                    allSelected = false;
                }
            });
            
            // Check for duplicates if all attributes are selected
            if (allSelected && isDuplicateVariation(attributeValues)) {
                alert('This variation combination already exists!');
                $(this).val(''); // Reset the current select
                return;
            }
            
            // Update the variation name display
            if (variationName) {
                nameDisplay.text(variationName);
            } else {
                nameDisplay.text('Select attributes');
            }
        });
        
        // Remove Variation
        $(document).on('click', '.remove-variation-btn', function() {
            if (confirm('Are you sure you want to remove this variation?')) {
                $(this).closest('.variation-card').remove();
            }
        });
        
        // Variation Image Preview
        $(document).on('change', '.variation-image-input', function() {
            const input = this;
            const $container = $(this).closest('.variation-image-upload');
            const $preview = $container.find('.image-preview-container');
            const variationIndex = $(this).data('variation-index');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Update the preview in the body
                    $preview.html(`
                        <img src="${e.target.result}" 
                             class="variation-image-preview" 
                             style="width: 100%; height: 100%; object-fit: cover;"
                             alt="Variation Image">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-variation-image" 
                                data-variation-index="${variationIndex}"
                                style="padding: 2px 6px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    `);
                    
                    // Update the thumbnail in the accordion header
                    const $accordionItem = $container.closest('.accordion-item');
                    const $headerImg = $accordionItem.find('.accordion-button .variation-header-image');
                    $headerImg.html(`
                        <img src="${e.target.result}" 
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                             alt="Variation">
                    `);
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        });
        
        // Remove Variation Image
        $(document).on('click', '.remove-variation-image', function() {
            const $container = $(this).closest('.variation-image-upload');
            const $preview = $container.find('.image-preview-container');
            
            // Clear the file input
            $container.find('.variation-image-input').val('');
            
            // Show placeholder in body
            $preview.html(`
                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                    <div class="text-center">
                        <i class="fas fa-image fa-2x mb-2"></i>
                        <div class="small">No Image</div>
                    </div>
                </div>
            `);
            
            // Reset the thumbnail in the accordion header
            const $accordionItem = $container.closest('.accordion-item');
            const $headerImg = $accordionItem.find('.accordion-button .variation-header-image');
            $headerImg.html(`
                <div class="d-flex align-items-center justify-content-center bg-light" 
                     style="width: 50px; height: 50px; border-radius: 4px;">
                    <i class="fas fa-image text-muted"></i>
                </div>
            `);
        });
        
        // Form Validation
        $('#product-form').on('submit', function(e) {
            const productType = $('input[name="product_type"]:checked').val();
            
            if (productType === 'variable') {
                // Check if at least one variation exists
                if ($('#variationsAccordion .variation-card').length === 0) {
                    e.preventDefault();
                    alert('Please add at least one variation for variable products.');
                    return false;
                }
                
                // Check if attributes are selected
                if (selectedAttributes.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one attribute for variable products.');
                    return false;
                }
                
                // Add product_attributes[] hidden inputs
                // First, remove any existing product_attributes inputs to avoid duplicates
                $('input[name="product_attributes[]"]').remove();
                
                // Add hidden input for each selected attribute
                $('.attribute-checkbox:checked').each(function() {
                    const attributeId = $(this).val();
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'product_attributes[]',
                        value: attributeId
                    }).appendTo('#product-form');
                });
            }
        });
    });
</script>

<style>
    .attribute-item {
        position: relative;
        padding: 8px;
        border-radius: 6px;
        transition: background-color 0.2s;
    }
    
    .attribute-item:hover {
        background-color: #f8f9fa;
    }
    
    .attribute-item .edit-attribute-btn {
        opacity: 0;
        transition: opacity 0.2s;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .attribute-item:hover .edit-attribute-btn {
        opacity: 1;
    }
    
    .attribute-value-row {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<!-- Attribute Modal -->
<div class="modal fade" id="attributeModal" tabindex="-1" aria-labelledby="attributeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attributeModalLabel">Add New Attribute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="attribute-form">
                    @csrf
                    <input type="hidden" id="attribute-id" name="attribute_id">
                    
                    <div class="mb-3">
                        <label for="attribute-name" class="form-label fw-bold">Attribute Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="attribute-name" name="name" placeholder="e.g., Size, Color, Material" required>
                        <div class="form-text">Enter the name of the attribute (e.g., Size, Color)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Attribute Values <span class="text-danger">*</span></label>
                        <div id="attribute-values-container">
                            <div class="input-group mb-2 attribute-value-row">
                                <input type="text" class="form-control attribute-value-input" name="values[]" placeholder="Enter value (e.g., Small, Red)" required>
                                <button type="button" class="btn btn-outline-danger remove-value-btn" disabled>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-value-btn">
                            <i class="fas fa-plus me-1"></i> Add Value
                        </button>
                        <div class="form-text">Add multiple values for this attribute</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="attribute-sort-order" class="form-label fw-bold">Sort Order</label>
                        <input type="number" class="form-control" id="attribute-sort-order" name="sort_order" value="0" min="0">
                        <div class="form-text">Lower numbers appear first</div>
                    </div>
                </form>
                
                <div id="attribute-modal-alert" class="alert d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-attribute-btn">
                    <i class="fas fa-save me-1"></i> Save Attribute
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let editingAttributeId = null;
    
    // Add Value Button
    $('#add-value-btn').on('click', function() {
        const newRow = `
            <div class="input-group mb-2 attribute-value-row">
                <input type="text" class="form-control attribute-value-input" name="values[]" placeholder="Enter value" required>
                <button type="button" class="btn btn-outline-danger remove-value-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        $('#attribute-values-container').append(newRow);
        updateRemoveButtons();
    });
    
    // Remove Value Button
    $(document).on('click', '.remove-value-btn', function() {
        $(this).closest('.attribute-value-row').remove();
        updateRemoveButtons();
    });
    
    function updateRemoveButtons() {
        const rows = $('.attribute-value-row');
        if (rows.length === 1) {
            rows.find('.remove-value-btn').prop('disabled', true);
        } else {
            rows.find('.remove-value-btn').prop('disabled', false);
        }
    }
    
    // Reset Modal on Open
    $('#attributeModal').on('show.bs.modal', function(e) {
        const button = $(e.relatedTarget);
        editingAttributeId = button.data('attribute-id');
        
        // Clear alert
        $('#attribute-modal-alert').addClass('d-none');
        
        if (editingAttributeId) {
            // Edit mode
            $('#attributeModalLabel').text('Edit Attribute');
            loadAttributeData(editingAttributeId);
        } else {
            // Create mode - Reset everything
            $('#attributeModalLabel').text('Add New Attribute');
            $('#attribute-form')[0].reset();
            $('#attribute-id').val('');
            $('#attribute-values-container').html(`
                <div class="input-group mb-2 attribute-value-row">
                    <input type="text" class="form-control attribute-value-input" name="values[]" placeholder="Enter value" required>
                    <button type="button" class="btn btn-outline-danger remove-value-btn" disabled>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
        }
    });
    
    // Reset Modal on Close - Clear all fields
    $('#attributeModal').on('hidden.bs.modal', function(e) {
        $('#attribute-form')[0].reset();
        $('#attribute-id').val('');
        $('#attribute-name').val('');
        $('#attribute-sort-order').val('0');
        $('#attribute-values-container').html(`
            <div class="input-group mb-2 attribute-value-row">
                <input type="text" class="form-control attribute-value-input" name="values[]" placeholder="Enter value" required>
                <button type="button" class="btn btn-outline-danger remove-value-btn" disabled>
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
        $('#attribute-modal-alert').addClass('d-none');
        $('#save-attribute-btn').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Attribute');
    });
    
    // Load Attribute Data for Editing
    function loadAttributeData(attributeId) {
        $.ajax({
            url: `/admin/attributes/${attributeId}/edit`,
            method: 'GET',
            success: function(response) {
                $('#attribute-id').val(response.id);
                $('#attribute-name').val(response.name);
                $('#attribute-sort-order').val(response.sort_order);
                
                // Clear and populate values
                $('#attribute-values-container').empty();
                response.values.forEach(function(value) {
                    const row = `
                        <div class="input-group mb-2 attribute-value-row">
                            <input type="text" class="form-control attribute-value-input" name="values[]" value="${value.value}" required>
                            <button type="button" class="btn btn-outline-danger remove-value-btn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    $('#attribute-values-container').append(row);
                });
                updateRemoveButtons();
            },
            error: function(xhr) {
                showAlert('Error loading attribute data', 'danger');
            }
        });
    }
    
    // Save Attribute
    $('#save-attribute-btn').on('click', function() {
        const attributeId = $('#attribute-id').val();
        
        // Validate
        if (!$('#attribute-name').val().trim()) {
            showAlert('Please enter an attribute name', 'danger');
            return;
        }
        
        const values = [];
        $('.attribute-value-input').each(function() {
            const val = $(this).val().trim();
            if (val) values.push(val);
        });
        
        if (values.length === 0) {
            showAlert('Please add at least one attribute value', 'danger');
            return;
        }
        
        // Disable button
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        
        const url = attributeId ? `/admin/attributes/${attributeId}` : '/admin/attributes';
        const method = attributeId ? 'PUT' : 'POST';
        
        // Build data object (not FormData) for proper array serialization
        const data = {
            name: $('#attribute-name').val().trim(),
            description: '', // Description field not in modal
            sort_order: $('#attribute-sort-order').val() || 0,
            values: values
        };
        
        console.log('Sending attribute data:', data);
        
        $.ajax({
            url: url,
            method: method,
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    
                    // If new attribute created, add it to the list
                    if (response.attribute && !attributeId) {
                        const attribute = response.attribute;
                        
                        // Remove "no attributes" message if it exists
                        $('#no-attributes-message').remove();
                        
                        // Build values string
                        const valuesStr = attribute.values.map(v => v.value).join(', ');
                        
                        // Build values JSON for data attribute
                        const valuesJson = {};
                        attribute.values.forEach(v => {
                            valuesJson[v.id] = v.value;
                        });
                        
                        // Create new attribute checkbox item
                        const attributeHtml = `
                            <div class="form-check mb-2 attribute-item" data-attribute-id="${attribute.id}">
                                <input class="form-check-input attribute-checkbox" type="checkbox" 
                                       id="attribute_${attribute.id}" 
                                       value="${attribute.id}" 
                                       data-attribute-name="${attribute.name}"
                                       data-attribute-values='${JSON.stringify(valuesJson)}'>
                                <label class="form-check-label" for="attribute_${attribute.id}">
                                    <strong>${attribute.name}</strong>
                                    <small class="text-muted d-block">
                                        Values: ${valuesStr}
                                    </small>
                                </label>
                                <button type="button" class="btn btn-sm btn-link text-primary p-0 ms-2 edit-attribute-btn" 
                                        data-attribute-id="${attribute.id}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#attributeModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        `;
                        
                        // Append to attribute selection
                        $('#attribute-selection').append(attributeHtml);
                        
                        // Close modal after short delay
                        setTimeout(function() {
                            $('#attributeModal').modal('hide');
                            $('#save-attribute-btn').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Attribute');
                        }, 1000);
                    } else if (attributeId) {
                        // If editing existing attribute, reload to update
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                } else {
                    showAlert(response.message || 'Error saving attribute', 'danger');
                    $('#save-attribute-btn').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Attribute');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error saving attribute';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                showAlert(errorMsg, 'danger');
                $('#save-attribute-btn').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Attribute');
            }
        });
    });
    
    function showAlert(message, type) {
        const alert = $('#attribute-modal-alert');
        alert.removeClass('d-none alert-success alert-danger alert-info')
            .addClass(`alert-${type}`)
            .html(message);
    }
});
</script>

<!-- Include CKEditor from CDN -->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
  CKEDITOR.replace('description', {
    versionCheck: false,
    toolbar: [
      { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
      { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'] },
      { name: 'links', items: ['Link', 'Unlink'] },
      { name: 'styles', items: ['Format'] },
      { name: 'tools', items: ['Maximize'] },
      { name: 'editing', items: ['Undo', 'Redo'] }
    ],
    height: 200
  });
</script>
@endsection