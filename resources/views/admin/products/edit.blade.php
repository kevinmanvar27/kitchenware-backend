@extends('admin.layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Edit Product'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Edit Product</h4>
                                    <p class="mb-0 text-muted">Modify product details</p>
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
                                
                                <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="product-form">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-4">
                                                <label for="name" class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4 py-2" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="description" class="form-label fw-bold">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="5" placeholder="Product description...">{{ old('description', $product->description) }}</textarea>
                                            </div>
                                            
                                            <!-- Product Type Display (Read-only for edit) -->
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Product Type</label>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    This is a <strong>{{ $product->isVariable() ? 'Variable' : 'Simple' }}</strong> product.
                                                    @if($product->isVariable())
                                                        Manage variations below.
                                                    @endif
                                                </div>
                                                <input type="hidden" name="product_type" value="{{ $product->product_type ?? 'simple' }}">
                                            </div>
                                            
                                            <!-- Simple Product Fields -->
                                            @if($product->isSimple())
                                            <div id="simple-product-fields">
                                                <div class="row">
                                                    <div class="col-md-6 mb-4">
                                                        <label for="mrp" class="form-label fw-bold">MRP (₹) <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="mrp" name="mrp" value="{{ old('mrp', $product->mrp) }}" step="0.01" min="0">
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        <label for="selling_price" class="form-label fw-bold">Selling Price (₹)</label>
                                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" step="0.01" min="0">
                                                        <div class="form-text">Must be less than or equal to MRP</div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            
                                            <!-- Variable Product Fields -->
                                            @if($product->isVariable())
                                            <div id="variable-product-fields">
                                                <!-- Display Selected Attributes -->
                                                <div class="mb-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="form-label fw-bold mb-0">Product Attributes</label>
                                                        <div>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill me-2" id="edit-attributes-btn">
                                                                <i class="fas fa-edit me-1"></i> Edit Attributes
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#attributeModal">
                                                                <i class="fas fa-plus me-1"></i> Add New Attribute
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="border rounded-3 p-3 bg-light" id="attributes-display">
                                                        @if($product->product_attributes && count($product->product_attributes) > 0)
                                                            @foreach($product->product_attributes as $attrId)
                                                                @php
                                                                    $attribute = $attributes->firstWhere('id', $attrId);
                                                                @endphp
                                                                @if($attribute)
                                                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                                                        <div>
                                                                            <strong>{{ $attribute->name }}:</strong>
                                                                            <span class="text-muted">{{ $attribute->values->pluck('value')->join(', ') }}</span>
                                                                        </div>
                                                                        <button type="button" class="btn btn-sm btn-link text-primary p-0 edit-attribute-btn" 
                                                                                data-attribute-id="{{ $attribute->id }}"
                                                                                data-bs-toggle="modal" 
                                                                                data-bs-target="#attributeModal">
                                                                            <i class="fas fa-edit"></i> Edit
                                                                        </button>
                                                                    </div>
                                                                    {{-- Hidden input to submit attribute ID --}}
                                                                    <input type="hidden" name="product_attributes[]" value="{{ $attrId }}">
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <p class="text-muted mb-0">No attributes assigned</p>
                                                        @endif
                                                    </div>
                                                    
                                                    {{-- Attribute Selection Panel (Hidden by default) --}}
                                                    <div class="border rounded-3 p-3 d-none" id="attributes-edit-panel">
                                                        <p class="text-muted small mb-3">Select attributes for this variable product. Changes will regenerate variations.</p>
                                                        @if(isset($attributes) && $attributes->count() > 0)
                                                            @foreach($attributes as $attribute)
                                                                <div class="form-check mb-2 attribute-item" data-attribute-id="{{ $attribute->id }}">
                                                                    <input class="form-check-input attribute-checkbox-edit" type="checkbox" 
                                                                           id="attribute_edit_{{ $attribute->id }}" 
                                                                           value="{{ $attribute->id }}" 
                                                                           data-attribute-name="{{ $attribute->name }}"
                                                                           data-attribute-values='@json($attribute->values->pluck('value', 'id'))'
                                                                           {{ in_array($attribute->id, $product->product_attributes ?? []) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="attribute_edit_{{ $attribute->id }}">
                                                                        <strong>{{ $attribute->name }}</strong>
                                                                        <small class="text-muted d-block">
                                                                            Values: {{ $attribute->values->pluck('value')->join(', ') }}
                                                                        </small>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                        <div class="mt-3">
                                                            <button type="button" class="btn btn-sm btn-success rounded-pill" id="save-attributes-btn">
                                                                <i class="fas fa-check me-1"></i> Save & Regenerate Variations
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-secondary rounded-pill" id="cancel-attributes-btn">
                                                                <i class="fas fa-times me-1"></i> Cancel
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Variations Table -->
                                                <div class="mb-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <label class="form-label fw-bold mb-0">Product Variations</label>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-sm btn-primary rounded-pill me-2" id="auto-generate-variations-btn">
                                                                <i class="fas fa-magic me-1"></i> Auto-Generate Variations
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="add-variation-btn">
                                                                <i class="fas fa-plus me-1"></i> Add Manual
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="accordion variations-container" id="variationsAccordion">
                                                        @foreach($product->variations as $index => $variation)
                                                        <div class="accordion-item variation-card" data-variation-id="{{ $variation->id }}" data-variation-index="{{ $index }}">
                                                            <h2 class="accordion-header" id="heading-{{ $index }}">
                                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}" aria-expanded="false" aria-controls="collapse-{{ $index }}">
                                                                    <div class="d-flex align-items-center w-100 me-3">
                                                                        <div class="variation-header-image me-3">
                                                                            @if($variation->image)
                                                                            <img src="{{ $variation->image_url }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;" alt="Variation">
                                                                            @else
                                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                                <i class="fas fa-image text-muted"></i>
                                                                            </div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="flex-grow-1">
                                                                            <strong class="text-primary">{{ $variation->display_name }}</strong>
                                                                            <div class="small text-muted">
                                                                                SKU: {{ $variation->sku ?? 'N/A' }} | 
                                                                                Price: ₹{{ number_format($variation->selling_price, 2) }} | 
                                                                                Stock: {{ $variation->stock_quantity }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="collapse-{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $index }}" data-bs-parent="#variationsAccordion">
                                                                <div class="accordion-body">
                                                                    <div class="row g-3">
                                                                        <!-- Variation Image -->
                                                                        <div class="col-md-2">
                                                                        <label class="form-label fw-bold small">Image</label>
                                                                        <div class="variation-image-upload">
                                                                            <div class="image-preview-container position-relative" style="width: 100%; height: 120px; border: 2px dashed #dee2e6; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                                                                                @if($variation->image)
                                                                                    <img src="{{ $variation->image_url }}" 
                                                                                         class="variation-image-preview" 
                                                                                         style="width: 100%; height: 100%; object-fit: cover;"
                                                                                         alt="Variation Image">
                                                                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-variation-image" 
                                                                                            data-variation-index="{{ $index }}"
                                                                                            style="padding: 2px 6px; font-size: 10px;">
                                                                                        <i class="fas fa-times"></i>
                                                                                    </button>
                                                                                @else
                                                                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                                                                        <div class="text-center">
                                                                                            <i class="fas fa-image fa-2x mb-2"></i>
                                                                                            <div class="small">No Image</div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                            <input type="file" 
                                                                                   class="form-control form-control-sm mt-2 variation-image-input" 
                                                                                   name="variations[{{ $index }}][image]" 
                                                                                   accept="image/*"
                                                                                   data-variation-index="{{ $index }}">
                                                                            <input type="hidden" 
                                                                                   name="variations[{{ $index }}][image_id]" 
                                                                                   value="{{ $variation->image_id }}"
                                                                                   class="variation-image-id">
                                                                            <input type="hidden" 
                                                                                   name="variations[{{ $index }}][remove_image]" 
                                                                                   value="0"
                                                                                   class="remove-image-flag">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Variation Details -->
                                                                    <div class="col-md-10">
                                                                        <div class="row g-3">
                                                                            <!-- Variation Name -->
                                                                            <div class="col-12">
                                                                                <div class="d-flex justify-content-between align-items-center">
                                                                                    <div>
                                                                                        <h6 class="mb-0 fw-bold text-primary">{{ $variation->display_name }}</h6>
                                                                                        <small class="text-muted">
                                                                                            @if($variation->formatted_attributes)
                                                                                                @foreach($variation->formatted_attributes as $attrName => $attrValue)
                                                                                                    <span class="badge bg-light text-dark border me-1">{{ $attrName }}: {{ $attrValue }}</span>
                                                                                                @endforeach
                                                                                            @endif
                                                                                        </small>
                                                                                    </div>
                                                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-variation-btn" title="Remove Variation">
                                                                                        <i class="fas fa-trash me-1"></i> Remove
                                                                                    </button>
                                                                                </div>
                                                                                <input type="hidden" name="variations[{{ $index }}][id]" value="{{ $variation->id }}">
                                                                                <input type="hidden" class="delete-flag" name="variations[{{ $index }}][_delete]" value="0">
                                                                                @foreach($variation->attribute_values as $attrId => $valueId)
                                                                                    <input type="hidden" name="variations[{{ $index }}][attribute_values][{{ $attrId }}]" value="{{ $valueId }}">
                                                                                @endforeach
                                                                            </div>
                                                                            
                                                                            <!-- SKU -->
                                                                            <div class="col-md-3">
                                                                                <label class="form-label small fw-bold">SKU</label>
                                                                                <input type="text" class="form-control form-control-sm" 
                                                                                       name="variations[{{ $index }}][sku]" 
                                                                                       value="{{ $variation->sku }}"
                                                                                       placeholder="Enter SKU">
                                                                            </div>
                                                                            
                                                                            <!-- MRP -->
                                                                            <div class="col-md-2">
                                                                                <label class="form-label small fw-bold">MRP (₹)</label>
                                                                                <input type="number" class="form-control form-control-sm" 
                                                                                       name="variations[{{ $index }}][mrp]" 
                                                                                       value="{{ $variation->mrp }}"
                                                                                       placeholder="MRP" 
                                                                                       step="0.01" min="0">
                                                                            </div>
                                                                            
                                                                            <!-- Selling Price -->
                                                                            <div class="col-md-2">
                                                                                <label class="form-label small fw-bold">Selling Price (₹)</label>
                                                                                <input type="number" class="form-control form-control-sm" 
                                                                                       name="variations[{{ $index }}][selling_price]" 
                                                                                       value="{{ $variation->selling_price }}"
                                                                                       placeholder="Price" 
                                                                                       step="0.01" min="0">
                                                                            </div>
                                                                            
                                                                            <!-- Stock Quantity -->
                                                                            <div class="col-md-2">
                                                                                <label class="form-label small fw-bold">
                                                                                    Stock <span class="text-danger">*</span>
                                                                                    @if($variation->stock_quantity <= ($variation->low_quantity_threshold ?? 10) && $variation->stock_quantity > 0)
                                                                                        <i class="fas fa-exclamation-triangle text-warning ms-1" title="Low Stock"></i>
                                                                                    @elseif($variation->stock_quantity == 0)
                                                                                        <i class="fas fa-times-circle text-danger ms-1" title="Out of Stock"></i>
                                                                                    @endif
                                                                                </label>
                                                                                <input type="number" class="form-control form-control-sm" 
                                                                                       name="variations[{{ $index }}][stock_quantity]" 
                                                                                       value="{{ $variation->stock_quantity ?? 0 }}"
                                                                                       placeholder="Stock" 
                                                                                       min="0"
                                                                                       required>
                                                                            </div>
                                                                            
                                                                            <!-- Low Stock Threshold -->
                                                                            <div class="col-md-2">
                                                                                <label class="form-label small fw-bold">Low Stock Alert</label>
                                                                                <input type="number" class="form-control form-control-sm" 
                                                                                       name="variations[{{ $index }}][low_quantity_threshold]" 
                                                                                       value="{{ $variation->low_quantity_threshold ?? 10 }}"
                                                                                       placeholder="Threshold" 
                                                                                       min="0">
                                                                            </div>
                                                                            
                                                                            <!-- Status -->
                                                                            <div class="col-md-1">
                                                                                <label class="form-label small fw-bold">Status</label>
                                                                                <div class="form-check form-switch">
                                                                                    <input class="form-check-input" type="checkbox" 
                                                                                           name="variations[{{ $index }}][in_stock]" 
                                                                                           value="1"
                                                                                           {{ $variation->in_stock ? 'checked' : '' }}>
                                                                                           
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
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            
                                            <!-- Stock Status - Only for Simple Products -->
                                            @if($product->isSimple())
                                            <div class="mb-4" id="simple-stock-fields">
                                                <label class="form-label fw-bold">Stock Status</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input type="hidden" name="in_stock" value="0">
                                                    <input class="form-check-input" type="checkbox" id="in_stock" name="in_stock" value="1" {{ old('in_stock', $product->in_stock) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="in_stock">
                                                        <span id="stock-status-text">{{ $product->in_stock ? 'In Stock' : 'Out of Stock' }}</span>
                                                    </label>
                                                </div>
                                                <div id="stock_quantity_container" class="{{ old('in_stock', $product->in_stock) ? '' : 'd-none' }}">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                                            <input type="number" class="form-control rounded-pill px-4 py-2" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="low_quantity_threshold" class="form-label">Low Stock Alert Threshold</label>
                                                            <input type="number" class="form-control rounded-pill px-4 py-2" id="low_quantity_threshold" name="low_quantity_threshold" value="{{ old('low_quantity_threshold', $product->low_quantity_threshold ?? 10) }}" min="0">
                                                            <div class="form-text text-muted">
                                                                <i class="fas fa-bell text-warning me-1"></i>
                                                                You'll receive a notification when stock falls below this quantity
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            <div class="mb-4">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Stock is managed at the variation level. Total stock: <strong>{{ $product->total_stock }}</strong>
                                                </div>
                                            </div>
                                            @endif
                                            
                                            <div class="mb-4">
                                                <label for="status" class="form-label fw-bold">Product Status <span class="text-danger">*</span></label>
                                                <select class="form-select rounded-pill px-4 py-2" id="status" name="status" required>
                                                    <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                                    <option value="published" {{ old('status', $product->status) == 'published' ? 'selected' : '' }}>Published</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Category Selection -->
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Categories</label>
                                                <div id="category-selection" class="border rounded-3 p-3" style="max-height: 400px; overflow-y: auto; background-color: #f8f9fa;">
                                                    @if(isset($categories) && $categories->count() > 0)
                                                        @foreach($categories as $category)
                                                            <div class="form-check mb-2 category-item" data-category-id="{{ $category->id }}">
                                                                <input class="form-check-input category-checkbox" type="checkbox" id="category_{{ $category->id }}" value="{{ $category->id }}" name="product_categories[{{ $category->id }}][category_id]" {{ in_array($category->id, old('product_categories', $product->categories->pluck('id')->toArray()) ?? []) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold" for="category_{{ $category->id }}">
                                                                    {{ $category->name }}
                                                                </label>
                                                                @if($category->subCategories->count() > 0)
                                                                    <div class="subcategory-container ms-4 mt-2 {{ in_array($category->id, old('product_categories', $product->categories->pluck('id')->toArray()) ?? []) ? '' : 'd-none' }}" id="subcategory_container_{{ $category->id }}">
                                                                        @foreach($category->subCategories as $subcategory)
                                                                            <div class="form-check mb-1">
                                                                                <input class="form-check-input subcategory-checkbox" type="checkbox" id="subcategory_{{ $subcategory->id }}" value="{{ $subcategory->id }}" name="product_categories[{{ $category->id }}][subcategory_ids][]" data-category-id="{{ $category->id }}" {{ in_array($subcategory->id, old('product_categories.' . $category->id . '.subcategory_ids', $product->subCategories->where('category_id', $category->id)->pluck('id')->toArray()) ?? []) ? 'checked' : '' }}>
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
                                                <div class="border rounded-3 p-3 text-center" id="main-photo-preview">
                                                    @if($product->main_photo)
                                                        <div class="position-relative" id="main-photo-display">
                                                            <img src="{{ $product->main_photo_url }}" class="img-fluid mb-2" alt="Main Photo" style="max-height: 200px; object-fit: contain;">
                                                            <div class="mt-2">
                                                                <label for="main_photo" class="btn btn-outline-primary btn-sm rounded-pill">
                                                                    <i class="fas fa-upload me-1"></i> Change Image
                                                                </label>
                                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill ms-2" id="remove-main-photo">
                                                                    <i class="fas fa-trash me-1"></i> Remove
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="upload-area" id="main-photo-display" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                                            <div>
                                                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                                <p class="text-muted mb-2">Click to select an image</p>
                                                                <label for="main_photo" class="btn btn-outline-primary btn-sm rounded-pill">
                                                                    <i class="fas fa-upload me-1"></i> Upload Image
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <!-- Keep file input outside preview area so it doesn't get removed -->
                                                <input type="file" id="main_photo" name="main_photo" class="d-none" accept="image/*">
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
                                                    <div id="gallery-preview" class="d-flex flex-wrap gap-2 mb-3">
                                                        @php
                                                            $galleryImages = [];
                                                            if ($product->product_gallery) {
                                                                $galleryImages = is_string($product->product_gallery) ? json_decode($product->product_gallery, true) : $product->product_gallery;
                                                            }
                                                        @endphp
                                                        @if(!empty($galleryImages) && is_array($galleryImages))
                                                            @foreach($galleryImages as $index => $imagePath)
                                                                <div class="position-relative gallery-item" data-index="{{ $index }}">
                                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 80px; width: 80px;">
                                                                        <img src="{{ asset('storage/' . $imagePath) }}" class="img-fluid" alt="Gallery Image" style="max-height: 100%; max-width: 100%; object-fit: cover;" onerror="this.parentElement.innerHTML='<i class=\'fas fa-image text-muted\'></i>'">
                                                                    </div>
                                                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle p-1 remove-gallery-item" data-index="{{ $index }}">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                    <input type="hidden" name="existing_gallery[]" value="{{ $imagePath }}">
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    <input type="hidden" id="product_gallery" name="product_gallery" value="{{ is_array($product->product_gallery) ? json_encode($product->product_gallery) : $product->product_gallery }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="border-top pt-4 mt-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0 fw-bold">SEO Settings</h5>
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" id="toggle-seo-settings">
                                                <i class="fas fa-chevron-down me-1"></i> {{ $product->meta_title || $product->meta_description || $product->meta_keywords ? 'Collapse' : 'Expand' }}
                                            </button>
                                        </div>
                                        
                                        <div id="seo-settings-content" class="{{ $product->meta_title || $product->meta_description || $product->meta_keywords ? '' : 'd-none' }}">
                                            <div class="mb-4">
                                                <label for="meta_title" class="form-label fw-bold">Meta Title</label>
                                                <input type="text" class="form-control rounded-pill px-4 py-2" id="meta_title" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" maxlength="255">
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="meta_description" class="form-label fw-bold">Meta Description</label>
                                                <textarea class="form-control" id="meta_description" name="meta_description" rows="3" maxlength="500" placeholder="Brief description for search engines...">{{ old('meta_description', $product->meta_description) }}</textarea>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="meta_keywords" class="form-label fw-bold">Meta Keywords</label>
                                                <input type="text" class="form-control rounded-pill px-4 py-2" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $product->meta_keywords) }}" placeholder="keyword1, keyword2, keyword3">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                                            <i class="fas fa-times me-2"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4 py-2">
                                            <i class="fas fa-save me-2"></i> Update Product
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
                    // Update only the display area, keep the input intact
                    $('#main-photo-display').replaceWith(`
                        <div class="position-relative" id="main-photo-display">
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
            $('#main-photo-display').replaceWith(`
                <div class="upload-area" id="main-photo-display" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
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
    
    // Variable Product Functionality for Edit
    @if($product->isVariable())
    let variationCounter = {{ $product->variations->count() }};
    let productAttributes = @json($product->product_attributes ?? []);
    let availableAttributes = @json($attributes);
    
    $(document).ready(function() {
        // Edit Attributes Button
        $('#edit-attributes-btn').on('click', function() {
            $('#attributes-display').addClass('d-none');
            $('#attributes-edit-panel').removeClass('d-none');
        });
        
        // Cancel Attributes Edit
        $('#cancel-attributes-btn').on('click', function() {
            $('#attributes-display').removeClass('d-none');
            $('#attributes-edit-panel').addClass('d-none');
            
            // Reset checkboxes to original state
            $('.attribute-checkbox-edit').each(function() {
                const attrId = parseInt($(this).val());
                $(this).prop('checked', productAttributes.includes(attrId));
            });
        });
        
        // Save Attributes and Regenerate Variations
        $('#save-attributes-btn').on('click', function() {
            const selectedAttrs = [];
            $('.attribute-checkbox-edit:checked').each(function() {
                const attrId = parseInt($(this).val());
                const attrName = $(this).data('attribute-name');
                const attrValues = $(this).data('attribute-values');
                selectedAttrs.push({
                    id: attrId,
                    name: attrName,
                    values: attrValues
                });
            });
            
            if (selectedAttrs.length === 0) {
                alert('Please select at least one attribute.');
                return;
            }
            
            if (!confirm('This will regenerate all variations. Existing variation data will be lost. Continue?')) {
                return;
            }
            
            // Update product attributes
            productAttributes = selectedAttrs.map(a => a.id);
            
            // Clear existing variations
            $('.variations-container').empty();
            variationCounter = 0;
            
            // Generate all combinations using the helper function
            const attributeData = [];
            selectedAttrs.forEach(attr => {
                const attribute = availableAttributes.find(a => a.id == attr.id);
                if (attribute && attribute.values && attribute.values.length > 0) {
                    attributeData.push({
                        id: attribute.id,
                        name: attribute.name,
                        values: attribute.values
                    });
                }
            });
            
            const combinations = generateCombinations(attributeData);
            
            // Add each combination as an accordion card
            combinations.forEach(combination => {
                const index = variationCounter++;
                const card = createVariationCard(index, combination);
                $('.variations-container').append(card);
            });
            
            // Update hidden inputs for product_attributes
            $('input[name="product_attributes[]"]').remove();
            productAttributes.forEach(attrId => {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'product_attributes[]',
                    value: attrId
                }).appendTo('#product-form');
            });
            
            // Update display
            updateAttributesDisplay(selectedAttrs);
            
            // Hide edit panel
            $('#attributes-display').removeClass('d-none');
            $('#attributes-edit-panel').addClass('d-none');
            
            alert(`Successfully generated ${combinations.length} variation(s)!`);
        });
        
        function updateAttributesDisplay(attrs) {
            let html = '';
            attrs.forEach(attr => {
                const attribute = availableAttributes.find(a => a.id == attr.id);
                if (attribute) {
                    const values = attribute.values.map(v => v.value).join(', ');
                    html += `
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${attribute.name}:</strong>
                                <span class="text-muted">${values}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-primary p-0 edit-attribute-btn" 
                                    data-attribute-id="${attribute.id}"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#attributeModal">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                        <input type="hidden" name="product_attributes[]" value="${attribute.id}">
                    `;
                }
            });
            $('#attributes-display').html(html);
        }
        
        // Auto-Generate All Variations
        $('#auto-generate-variations-btn').on('click', function() {
            if (productAttributes.length === 0) {
                alert('This product has no attributes assigned. Please add attributes first.');
                return;
            }
            
            if (!confirm('This will generate all possible variations based on your product attributes. Continue?')) {
                return;
            }
            
            // Get all attribute values for each attribute
            const attributeData = [];
            productAttributes.forEach(attrId => {
                const attribute = availableAttributes.find(a => a.id == attrId);
                if (attribute && attribute.values && attribute.values.length > 0) {
                    attributeData.push({
                        id: attribute.id,
                        name: attribute.name,
                        values: attribute.values
                    });
                }
            });
            
            if (attributeData.length === 0) {
                alert('No attribute values found. Please add values to your attributes first.');
                return;
            }
            
            // Generate all combinations
            const combinations = generateCombinations(attributeData);
            
            if (combinations.length === 0) {
                alert('No combinations could be generated.');
                return;
            }
            
            let generatedCount = 0;
            let skippedCount = 0;
            
            // Create a variation card for each combination
            combinations.forEach(combination => {
                // Check if this combination already exists
                if (isVariationExists(combination)) {
                    skippedCount++;
                    return;
                }
                
                const index = variationCounter++;
                const card = createVariationCard(index, combination);
                $('.variations-container').append(card);
                generatedCount++;
            });
            
            if (generatedCount > 0) {
                alert(`Generated ${generatedCount} new variation(s). ${skippedCount > 0 ? `Skipped ${skippedCount} existing variation(s).` : ''}`);
            } else {
                alert('All possible variations already exist.');
            }
        });
        
        // Helper: Generate all combinations of attribute values
        function generateCombinations(attributeData) {
            if (attributeData.length === 0) return [];
            
            function combine(arrays) {
                if (arrays.length === 0) return [[]];
                
                const [first, ...rest] = arrays;
                const combos = combine(rest);
                const result = [];
                
                first.forEach(value => {
                    combos.forEach(combo => {
                        result.push([value, ...combo]);
                    });
                });
                
                return result;
            }
            
            const valueArrays = attributeData.map(attr => 
                attr.values.map(val => ({
                    attributeId: attr.id,
                    attributeName: attr.name,
                    valueId: val.id,
                    valueName: val.value
                }))
            );
            
            return combine(valueArrays);
        }
        
        // Helper: Check if variation with these attributes already exists
        function isVariationExists(combination) {
            let exists = false;
            
            $('.variation-card').each(function() {
                const $card = $(this);
                
                // Skip deleted cards
                if ($card.find('.delete-flag').val() == '1' || $card.css('display') === 'none') {
                    return true; // continue
                }
                
                let matches = true;
                
                // Check if all attributes match
                combination.forEach(attr => {
                    const existingValue = $card.find(`select[name*="[attribute_values][${attr.attributeId}]"], input[name*="[attribute_values][${attr.attributeId}]"]`).val();
                    if (existingValue != attr.valueId) {
                        matches = false;
                    }
                });
                
                if (matches) {
                    exists = true;
                    return false; // break
                }
            });
            
            return exists;
        }
        
        // Helper: Create variation card with pre-filled attributes (Accordion Style)
        function createVariationCard(index, combination = null) {
            let attributeSelectors = '';
            let variationName = '';
            let attributeBadges = '';
            
            // Build attribute selectors
            productAttributes.forEach(attrId => {
                const attribute = availableAttributes.find(a => a.id == attrId);
                if (!attribute) return;
                
                // Find the pre-selected value for this attribute (if auto-generated)
                const selectedValue = combination ? combination.find(c => c.attributeId == attrId) : null;
                
                const options = attribute.values.map(v => {
                    const selected = selectedValue && v.id == selectedValue.valueId ? 'selected' : '';
                    return `<option value="${v.id}" ${selected}>${v.value}</option>`;
                }).join('');
                
                attributeSelectors += `
                    <div class="mb-2">
                        <label class="form-label small fw-bold">${attribute.name}</label>
                        <select class="form-select form-select-sm variation-attr-select" 
                                name="variations[${index}][attribute_values][${attribute.id}]" 
                                data-attribute-id="${attribute.id}"
                                data-attribute-name="${attribute.name}"
                                data-variation-index="${index}"
                                required>
                            <option value="">Select ${attribute.name}</option>
                            ${options}
                        </select>
                    </div>
                `;
                
                // Build attribute badges for header
                if (selectedValue) {
                    attributeBadges += `<span class="badge bg-light text-dark border me-1">${attribute.name}: ${selectedValue.valueName}</span>`;
                }
            });
            
            // Build variation name if combination provided
            if (combination) {
                variationName = combination.map(c => `${c.attributeName}: ${c.valueName}`).join(' | ');
            } else {
                variationName = 'Select attributes';
            }
            
            const card = `
                <div class="accordion-item variation-card" data-variation-index="${index}" data-is-new="true">
                    <h2 class="accordion-header" id="heading-new-${index}">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-new-${index}" aria-expanded="true" aria-controls="collapse-new-${index}">
                            <div class="d-flex align-items-center w-100 me-3">
                                <div class="variation-header-image me-3">
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="text-primary variation-display-name">${variationName}</strong>
                                    <div class="small text-muted variation-summary">
                                        ${attributeBadges || 'New variation - configure attributes below'}
                                    </div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-new-${index}" class="accordion-collapse collapse show" aria-labelledby="heading-new-${index}" data-bs-parent="#variationsAccordion">
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
                                        <!-- Attributes Section -->
                                        <div class="col-12">
                                            <h6 class="fw-bold text-secondary mb-2">Attributes</h6>
                                            <div class="attribute-selectors">
                                                ${attributeSelectors}
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
            
            return card;
        }
        
        // Add New Variation (Manual)
        $('#add-variation-btn').on('click', function() {
            if (productAttributes.length === 0) {
                alert('This product has no attributes assigned. Please add attributes first.');
                return;
            }
            
            const index = variationCounter++;
            const card = createVariationCard(index);
            $('.variations-container').append(card);
        });
        
        // Update variation name when attributes are selected
        $(document).on('change', '.variation-attr-select', function() {
            const index = $(this).data('variation-index');
            const $card = $(`.variation-card[data-variation-index="${index}"]`);
            const $selects = $card.find('.variation-attr-select');
            const $nameDisplay = $card.find('.variation-display-name');
            const $summaryDisplay = $card.find('.variation-summary');
            
            let allSelected = true;
            let variationName = '';
            let attributeValues = {};
            let attributeBadges = '';
            
            $selects.each(function(idx) {
                const $select = $(this);
                const selectedValue = $select.val();
                const selectedText = $select.find('option:selected').text();
                const attrId = $select.data('attribute-id');
                const attrName = $select.data('attribute-name');
                
                if (!selectedValue) {
                    allSelected = false;
                } else {
                    if (idx > 0) variationName += ' | ';
                    variationName += `${attrName}: ${selectedText}`;
                    attributeValues[attrId] = selectedValue;
                    attributeBadges += `<span class="badge bg-light text-dark border me-1">${attrName}: ${selectedText}</span>`;
                }
            });
            
            // Update display name in accordion header and body
            if (allSelected && variationName) {
                $nameDisplay.text(variationName);
                if ($summaryDisplay.length) {
                    $summaryDisplay.html(attributeBadges);
                }
                
                // Check for duplicates
                if (isDuplicateVariation(attributeValues, index)) {
                    alert('This variation combination already exists!');
                    $(this).val(''); // Reset the current select
                    $nameDisplay.text('Select attributes');
                    if ($summaryDisplay.length) {
                        $summaryDisplay.text('New variation - configure attributes below');
                    }
                    return;
                }
            } else {
                $nameDisplay.text('Select attributes');
                if ($summaryDisplay.length) {
                    $summaryDisplay.text('New variation - configure attributes below');
                }
            }
        });
        
        // Check for duplicate variation combinations
        function isDuplicateVariation(attributeValues, currentIndex) {
            let isDuplicate = false;
            $('.variation-card').each(function() {
                const $card = $(this);
                const cardIndex = $card.data('variation-index');
                
                // Skip current card and deleted cards
                if (cardIndex == currentIndex || $card.find('.delete-flag').val() == '1') {
                    return true; // continue
                }
                
                let matches = true;
                
                // Check if all attribute values match
                for (const [attrId, valueId] of Object.entries(attributeValues)) {
                    const existingValue = $card.find(`select[name*="[attribute_values][${attrId}]"], input[name*="[attribute_values][${attrId}]"]`).val();
                    if (existingValue != valueId) {
                        matches = false;
                        break;
                    }
                }
                
                if (matches && Object.keys(attributeValues).length > 0) {
                    isDuplicate = true;
                    return false; // break the loop
                }
            });
            
            return isDuplicate;
        }
        
        // Remove Variation
        $(document).on('click', '.remove-variation-btn', function() {
            const $card = $(this).closest('.variation-card');
            const variationId = $card.data('variation-id');
            
            if (confirm('Are you sure you want to remove this variation?')) {
                if (variationId) {
                    // Mark for deletion
                    $card.find('.delete-flag').val('1');
                    $card.hide();
                } else {
                    // New variation, just remove
                    $card.remove();
                }
            }
        });
        
        // Variation Image Preview
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
            const variationIndex = $(this).data('variation-index');
            const $container = $(this).closest('.variation-image-upload');
            const $preview = $container.find('.image-preview-container');
            
            // Clear the file input
            $container.find('.variation-image-input').val('');
            
            // Mark image for removal
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
            const $headerImg = $accordionItem.find('.accordion-button .variation-header-image');
            $headerImg.html(`
                <div class="d-flex align-items-center justify-content-center bg-light" 
                     style="width: 50px; height: 50px; border-radius: 4px;">
                    <i class="fas fa-image text-muted"></i>
                </div>
            `);
        });
    });
    @endif
</script>

<style>
    .edit-attribute-btn {
        transition: all 0.2s;
        text-decoration: none !important;
    }
    
    .edit-attribute-btn:hover {
        transform: scale(1.1);
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
                    
                    // If new attribute created, add it to the edit panel
                    if (response.attribute && !attributeId) {
                        const attribute = response.attribute;
                        
                        // Build values string
                        const valuesStr = attribute.values.map(v => v.value).join(', ');
                        
                        // Build values JSON for data attribute
                        const valuesJson = {};
                        attribute.values.forEach(v => {
                            valuesJson[v.id] = v.value;
                        });
                        
                        // Create new attribute checkbox item for edit panel
                        const attributeHtml = `
                            <div class="form-check mb-2 attribute-item" data-attribute-id="${attribute.id}">
                                <input class="form-check-input attribute-checkbox-edit" type="checkbox" 
                                       id="attribute_edit_${attribute.id}" 
                                       value="${attribute.id}" 
                                       data-attribute-name="${attribute.name}"
                                       data-attribute-values='${JSON.stringify(valuesJson)}'>
                                <label class="form-check-label" for="attribute_edit_${attribute.id}">
                                    <strong>${attribute.name}</strong>
                                    <small class="text-muted d-block">
                                        Values: ${valuesStr}
                                    </small>
                                </label>
                            </div>
                        `;
                        
                        // Find the save button container and insert before it
                        $('#attributes-edit-panel .mt-3').before(attributeHtml);
                        
                        // Also update availableAttributes array for auto-generation
                        availableAttributes.push({
                            id: attribute.id,
                            name: attribute.name,
                            values: attribute.values
                        });
                        
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