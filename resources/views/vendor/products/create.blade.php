@extends('vendor.layouts.app')

@section('title', 'Create Product')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Create Product'])
            
            @section('page-title', 'Create Product')
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Create New Product</h4>
                                    <p class="mb-0 text-muted">Add a new product to your store</p>
                                </div>
                                <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Products
                                </a>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                <form action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
                                    @csrf
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <!-- Basic Information -->
                                            <div class="mb-4">
                                                <label for="name" class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4 py-2" id="name" name="name" value="{{ old('name') }}" required>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="description" class="form-label fw-bold">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="5" placeholder="Product description...">{{ old('description') }}</textarea>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="short_description" class="form-label fw-bold">Short Description</label>
                                                <textarea class="form-control" id="short_description" name="short_description" rows="2" placeholder="Brief product summary...">{{ old('short_description') }}</textarea>
                                            </div>
                                            
                                            <!-- Product Type -->
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
                                            
                                            <!-- Pricing (Simple Product) -->
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
                                                
                                                <!-- SKU -->
                                                <div class="mb-4">
                                                    <label for="sku" class="form-label fw-bold">SKU</label>
                                                    <input type="text" class="form-control rounded-pill px-4 py-2" id="sku" name="sku" value="{{ old('sku') }}">
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
                                            
                                            <!-- Variable Product Fields -->
                                            <div id="variable-product-fields" class="{{ old('product_type') == 'variable' ? '' : 'd-none' }}">
                                                <!-- Attribute Selection -->
                                                <div class="mb-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="form-label fw-bold mb-0">Product Attributes <span class="text-danger">*</span></label>
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
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <p class="text-muted mb-0" id="no-attributes-message">No attributes available. Please contact admin to create attributes.</p>
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
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <!-- Status -->
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-light">
                                                            <h6 class="mb-0 fw-bold">Publish</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="mb-3">
                                                                <label for="status" class="form-label fw-bold">Status</label>
                                                                <select class="form-select rounded-pill" id="status" name="status">
                                                                    <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                                                                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                                                </select>
                                                            </div>
                                                            <button type="submit" class="btn btn-theme w-100 rounded-pill">
                                                                <i class="fas fa-save me-2"></i>Save Product
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <!-- Categories -->
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-light">
                                                            <h6 class="mb-0 fw-bold">Categories</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="category-list" style="max-height: 400px; overflow-y: auto; background-color: #f8f9fa; padding: 10px; border-radius: 8px;">
                                                                @foreach($categories ?? [] as $category)
                                                                    <div class="form-check mb-2 category-item" data-category-id="{{ $category->id }}">
                                                                        <input class="form-check-input category-checkbox" type="checkbox" id="cat_{{ $category->id }}" value="{{ $category->id }}" name="product_categories[{{ $category->id }}][category_id]">
                                                                        <label class="form-check-label fw-bold" for="cat_{{ $category->id }}">{{ $category->name }}</label>
                                                                        @if($category->subCategories && $category->subCategories->count() > 0)
                                                                            <div class="subcategory-container ms-4 mt-2 d-none" id="subcategory_container_{{ $category->id }}">
                                                                                @foreach($category->subCategories as $subcategory)
                                                                                    <div class="form-check mb-1">
                                                                                        <input class="form-check-input subcategory-checkbox" type="checkbox" id="subcat_{{ $subcategory->id }}" value="{{ $subcategory->id }}" name="product_categories[{{ $category->id }}][subcategory_ids][]" data-category-id="{{ $category->id }}">
                                                                                        <label class="form-check-label" for="subcat_{{ $subcategory->id }}">
                                                                                            {{ $subcategory->name }}
                                                                                        </label>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <!-- Product Image -->
                                            <div class="card mb-4">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0 fw-bold">Product Image</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="main-image-preview" class="mb-2">
                                                        <div class="upload-area" id="main-image-upload-area" style="min-height: 180px; border: 2px dashed #dee2e6; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                                            <div class="text-center">
                                                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                                <p class="text-muted mb-2 small">Click to select an image</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="file" class="form-control d-none" id="main_photo" name="main_photo" accept="image/*">
                                                    <div class="form-text mt-2">Recommended: 800x800 pixels (Max: 2MB)</div>
                                                </div>
                                            </div>
                                            
                                            <!-- Gallery Images -->
                                            <div class="card mb-4">
                                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0 fw-bold">Gallery Images</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="gallery-preview" class="row g-2 mb-2">
                                                        <div class="col-12 text-center text-muted py-3" id="gallery-empty-state">
                                                            <i class="fas fa-images fa-2x mb-2"></i>
                                                            <p class="small mb-0">No gallery images selected</p>
                                                        </div>
                                                    </div>
                                                    <input type="file" class="form-control" id="gallery_images" name="gallery_images[]" accept="image/*" multiple>
                                                    <div class="form-text mt-2">You can select multiple images (Max: 2MB each)</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Variable Product Functionality
    let selectedAttributes = [];
    let variationCounter = 0;
    
    $(document).ready(function() {
        // Product type toggle
        $('input[name="product_type"]').on('change', function() {
            const productType = $(this).val();
            
            if (productType === 'simple') {
                $('#simple-product-fields').removeClass('d-none');
                $('#simple-stock-fields').removeClass('d-none');
                $('#variable-product-fields').addClass('d-none');
                
                // Make simple product fields required
                $('#mrp').prop('required', true);
            } else {
                $('#simple-product-fields').addClass('d-none');
                $('#simple-stock-fields').addClass('d-none');
                $('#variable-product-fields').removeClass('d-none');
                
                // Remove required from simple product fields
                $('#mrp').prop('required', false);
            }
        });
        
        // Handle stock status toggle
        function handleStockStatusToggle() {
            const $inStockCheckbox = $('#in_stock');
            const $stockQuantityContainer = $('#stock_quantity_container');
            const $stockStatusText = $('#stock-status-text');
            const $stockQuantityInput = $('#stock_quantity');
            
            if ($inStockCheckbox.length && $stockQuantityContainer.length) {
                // Initial state
                if ($inStockCheckbox.is(':checked')) {
                    $stockQuantityContainer.removeClass('d-none');
                    if ($stockStatusText.length) {
                        $stockStatusText.text('In Stock');
                    }
                } else {
                    $stockQuantityContainer.addClass('d-none');
                    if ($stockStatusText.length) {
                        $stockStatusText.text('Out of Stock');
                    }
                    // Set stock quantity to 0 when unchecked
                    $stockQuantityInput.val(0);
                }
                
                // Add event listener for checkbox change
                $inStockCheckbox.on('change', function() {
                    if ($(this).is(':checked')) {
                        $stockQuantityContainer.removeClass('d-none');
                        if ($stockStatusText.length) {
                            $stockStatusText.text('In Stock');
                        }
                    } else {
                        $stockQuantityContainer.addClass('d-none');
                        if ($stockStatusText.length) {
                            $stockStatusText.text('Out of Stock');
                        }
                        // Set stock quantity to 0 when unchecked
                        $stockQuantityInput.val(0);
                    }
                });
                
                // Add event listener for stock quantity change
                $stockQuantityInput.on('input change', function() {
                    const quantity = parseInt($(this).val()) || 0;
                    
                    // If quantity is 0, uncheck the in_stock toggle
                    if (quantity === 0) {
                        $inStockCheckbox.prop('checked', false);
                        $stockQuantityContainer.addClass('d-none');
                        if ($stockStatusText.length) {
                            $stockStatusText.text('Out of Stock');
                        }
                    } else {
                        // If quantity is greater than 0, check the in_stock toggle
                        if (!$inStockCheckbox.is(':checked')) {
                            $inStockCheckbox.prop('checked', true);
                            $stockQuantityContainer.removeClass('d-none');
                            if ($stockStatusText.length) {
                                $stockStatusText.text('In Stock');
                            }
                        }
                    }
                });
            }
        }
        
        // Initialize stock status toggle
        handleStockStatusToggle();
        
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
                
                selectedAttributes.push({
                    id: attributeId,
                    name: attributeName,
                    values: attributeValues
                });
            });
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
            
            // Confirm if variations already exist
            if ($('#variationsAccordion .accordion-item').length > 0) {
                if (!confirm('This will replace all existing variations. Continue?')) {
                    return;
                }
            }
            
            // Generate all combinations
            const combinations = generateCombinations(selectedAttributes);
            
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
                    return;
                }
            }
            
            const index = variationCounter++;
            
            // Build variation name
            let variationName = '';
            let attributeInputs = '';
            const combinationEntries = Object.entries(combination);
            
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
                                        <input type="hidden" name="variations[${index}][image_id]" class="variation-image-id" data-variation-index="${index}">
                                        <input type="hidden" name="variations[${index}][remove_image]" value="0" class="remove-image-flag">
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
                                    
                                    <!-- Stock Status -->
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Stock Status</label>
                                        <div class="form-check form-switch mb-2">
                                            <input type="hidden" name="variations[${index}][in_stock]" value="0">
                                            <input class="form-check-input variation-stock-toggle" type="checkbox" 
                                                   id="variation_stock_${index}"
                                                   name="variations[${index}][in_stock]" 
                                                   value="1" checked
                                                   data-variation-index="${index}">
                                            <label class="form-check-label" for="variation_stock_${index}">
                                                <span class="variation-stock-status-text">In Stock</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Stock Quantity -->
                                    <div class="col-md-2 variation-stock-quantity-container" id="variation_stock_container_${index}">
                                        <label class="form-label small fw-bold">Stock Quantity</label>
                                        <input type="number" class="form-control form-control-sm variation-stock-quantity" 
                                               name="variations[${index}][stock_quantity]" 
                                               value="0" 
                                               placeholder="Stock" 
                                               min="0"
                                               data-variation-index="${index}">
                                    </div>
                                    
                                    <!-- Low Stock Threshold -->
                                    <div class="col-md-2 variation-stock-quantity-container" id="variation_threshold_container_${index}">
                                        <label class="form-label small fw-bold">Low Stock Alert</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][low_quantity_threshold]" 
                                               value="10" 
                                               placeholder="Threshold" 
                                               min="0">
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
                                    <input type="hidden" name="variations[${index}][image_id]" class="variation-image-id" data-variation-index="${index}">
                                    <input type="hidden" name="variations[${index}][remove_image]" value="0" class="remove-image-flag">
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
                                    
                                    <!-- Stock Status -->
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Stock Status</label>
                                        <div class="form-check form-switch mb-2">
                                            <input type="hidden" name="variations[${index}][in_stock]" value="0">
                                            <input class="form-check-input variation-stock-toggle" type="checkbox" 
                                                   id="variation_stock_${index}"
                                                   name="variations[${index}][in_stock]" 
                                                   value="1" checked
                                                   data-variation-index="${index}">
                                            <label class="form-check-label" for="variation_stock_${index}">
                                                <span class="variation-stock-status-text">In Stock</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Stock Quantity -->
                                    <div class="col-md-2 variation-stock-quantity-container" id="variation_stock_container_${index}">
                                        <label class="form-label small fw-bold">Stock Quantity</label>
                                        <input type="number" class="form-control form-control-sm variation-stock-quantity" 
                                               name="variations[${index}][stock_quantity]" 
                                               value="0" 
                                               placeholder="Stock" 
                                               min="0"
                                               data-variation-index="${index}">
                                    </div>
                                    
                                    <!-- Low Stock Threshold -->
                                    <div class="col-md-2 variation-stock-quantity-container" id="variation_threshold_container_${index}">
                                        <label class="form-label small fw-bold">Low Stock Alert</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][low_quantity_threshold]" 
                                               value="10" 
                                               placeholder="Threshold" 
                                               min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#variationsAccordion').append(card);
        });
        
        // Remove variation
        $(document).on('click', '.remove-variation-btn', function() {
            $(this).closest('.variation-card').remove();
        });
        
        // Update variation name when attributes are selected (for manual variations)
        $(document).on('change', '.variation-attribute-select', function() {
            const card = $(this).closest('.variation-card');
            const nameDisplay = card.find('.variation-name-display');
            
            let nameParts = [];
            card.find('.variation-attribute-select').each(function() {
                const selectedOption = $(this).find('option:selected');
                if (selectedOption.val()) {
                    nameParts.push(selectedOption.text());
                }
            });
            
            if (nameParts.length > 0) {
                nameDisplay.text(nameParts.join(' - '));
            } else {
                nameDisplay.text('Select attributes');
            }
        });
        
        // Handle variation stock status toggle
        $(document).on('change', '.variation-stock-toggle', function() {
            const variationIndex = $(this).data('variation-index');
            const $stockContainer = $(`#variation_stock_container_${variationIndex}`);
            const $thresholdContainer = $(`#variation_threshold_container_${variationIndex}`);
            const $statusText = $(this).siblings('label').find('.variation-stock-status-text');
            const $stockQuantityInput = $(this).closest('.row').find(`.variation-stock-quantity[data-variation-index="${variationIndex}"]`);
            
            if ($(this).is(':checked')) {
                $stockContainer.removeClass('d-none');
                $thresholdContainer.removeClass('d-none');
                $statusText.text('In Stock');
            } else {
                $stockContainer.addClass('d-none');
                $thresholdContainer.addClass('d-none');
                $statusText.text('Out of Stock');
                $stockQuantityInput.val(0);
            }
        });
        
        // Handle variation stock quantity change
        $(document).on('input change', '.variation-stock-quantity', function() {
            const variationIndex = $(this).data('variation-index');
            const quantity = parseInt($(this).val()) || 0;
            const $toggle = $(`#variation_stock_${variationIndex}`);
            const $stockContainer = $(`#variation_stock_container_${variationIndex}`);
            const $thresholdContainer = $(`#variation_threshold_container_${variationIndex}`);
            const $statusText = $toggle.siblings('label').find('.variation-stock-status-text');
            
            if (quantity === 0) {
                $toggle.prop('checked', false);
                $stockContainer.addClass('d-none');
                $thresholdContainer.addClass('d-none');
                $statusText.text('Out of Stock');
            } else {
                if (!$toggle.is(':checked')) {
                    $toggle.prop('checked', true);
                    $stockContainer.removeClass('d-none');
                    $thresholdContainer.removeClass('d-none');
                    $statusText.text('In Stock');
                }
            }
        });
        
        // Variation image preview
        $(document).on('change', '.variation-image-input', function() {
            const input = this;
            const $container = $(this).closest('.variation-image-upload');
            const $preview = $container.find('.image-preview-container');
            
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
                                data-variation-index="${$(input).data('variation-index')}"
                                style="padding: 2px 6px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    `);
                    
                    // Update the thumbnail in the accordion header
                    const $accordionItem = $container.closest('.accordion-item');
                    if ($accordionItem.length) {
                        const $headerImg = $accordionItem.find('.accordion-button .variation-header-image');
                        $headerImg.html(`
                            <img src="${e.target.result}" 
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                 alt="Variation">
                        `);
                    }
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        });
        
        // Remove Variation Image
        $(document).on('click', '.remove-variation-image', function() {
            const variationIndex = $(this).data('variation-index');
            const $container = $(this).closest('.variation-image-upload');
            const $preview = $container.find('.image-preview-container');
            
            // Clear the file input
            $container.find('.variation-image-input').val('');
            
            // Mark image for removal and clear the image_id
            $container.find('.remove-image-flag').val('1');
            $container.find('.variation-image-id').val('');
            
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
            if ($accordionItem.length) {
                const $headerImg = $accordionItem.find('.accordion-button .variation-header-image');
                $headerImg.html(`
                    <div class="d-flex align-items-center justify-content-center bg-light" 
                         style="width: 50px; height: 50px; border-radius: 4px;">
                        <i class="fas fa-image text-muted"></i>
                    </div>
                `);
            }
        });
        
        // Form validation before submit
        $('#product-form').on('submit', function(e) {
            const productType = $('input[name="product_type"]:checked').val();
            
            if (productType === 'variable') {
                // Check if attributes are selected
                if (selectedAttributes.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one attribute for variable product.');
                    return false;
                }
                
                // Check if variations exist
                if ($('#variationsAccordion .variation-card').length === 0) {
                    e.preventDefault();
                    alert('Please add at least one variation for variable product.');
                    return false;
                }
                
                // Add product_attributes[] hidden inputs
                $('input[name="product_attributes[]"]').remove();
                
                selectedAttributes.forEach(attr => {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'product_attributes[]',
                        value: attr.id
                    }).appendTo('#product-form');
                });
            }
            
            return true;
        });
        
        // ========== Image Upload Functions ==========
        
        // Main image upload
        $('#main-image-upload-area').on('click', function() {
            $('#main_photo').click();
        });
        
        $('#main_photo').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#main-image-preview').html(`
                        <div class="position-relative">
                            <img src="${e.target.result}" class="img-fluid rounded" alt="Main Image" style="max-height: 200px; object-fit: contain; width: 100%;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeMainImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Gallery images preview
        $('#gallery_images').on('change', function() {
            const files = this.files;
            if (files.length > 0) {
                $('#gallery-empty-state').remove();
                let html = '';
                Array.from(files).forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imgHtml = `
                            <div class="col-4 col-md-6 mb-2 gallery-item" data-index="${index}">
                                <div class="position-relative">
                                    <img src="${e.target.result}" class="img-fluid rounded" alt="Gallery Image" style="height: 80px; width: 100%; object-fit: cover;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle" style="width: 24px; height: 24px; padding: 0; font-size: 10px;" onclick="removeGalleryImage(${index})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        $('#gallery-preview').append(imgHtml);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    });
    
    // Remove main image
    function removeMainImage() {
        $('#main_photo').val('');
        $('#main-image-preview').html(`
            <div class="upload-area" id="main-image-upload-area" style="min-height: 180px; border: 2px dashed #dee2e6; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div class="text-center">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2 small">Click to select an image</p>
                </div>
            </div>
        `);
        // Re-attach click handler
        $('#main-image-upload-area').on('click', function() {
            $('#main_photo').click();
        });
    }
    
    // Remove gallery image
    function removeGalleryImage(index) {
        $(`.gallery-item[data-index="${index}"]`).remove();
        if ($('#gallery-preview .gallery-item').length === 0) {
            $('#gallery-preview').html(`
                <div class="col-12 text-center text-muted py-3" id="gallery-empty-state">
                    <i class="fas fa-images fa-2x mb-2"></i>
                    <p class="small mb-0">No gallery images selected</p>
                </div>
            `);
        }
    }
    
    // Category/Subcategory Toggle Functionality
    $(document).ready(function() {
        // Initialize: Show subcategories for already checked categories
        $('.category-checkbox:checked').each(function() {
            const categoryId = $(this).val();
            $('#subcategory_container_' + categoryId).removeClass('d-none');
        });
        
        // Handle category checkbox changes
        $('.category-checkbox').on('change', function() {
            const categoryId = $(this).val();
            const $subcategoryContainer = $('#subcategory_container_' + categoryId);
            
            if ($(this).is(':checked')) {
                // Show subcategories when category is selected
                $subcategoryContainer.removeClass('d-none');
            } else {
                // Hide and deselect subcategories when category is deselected
                $subcategoryContainer.addClass('d-none');
                $subcategoryContainer.find('.subcategory-checkbox').prop('checked', false);
            }
        });
    });
</script>
@endsection

@section('styles')
<style>
    .upload-area.drag-over {
        border-color: var(--theme-color, #FF6B00) !important;
        background-color: rgba(255, 107, 0, 0.05);
    }
    
    .upload-area {
        transition: all 0.2s ease;
    }
    
    .upload-area:hover {
        border-color: var(--theme-color, #FF6B00);
        background-color: rgba(255, 107, 0, 0.03);
    }
    
    .media-item {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    
    .media-item:hover {
        border-color: var(--theme-color, #FF6B00);
    }
    
    .media-item.selected {
        border-color: var(--theme-color, #FF6B00);
        background-color: rgba(255, 107, 0, 0.1);
    }
</style>

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
