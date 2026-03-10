@extends('vendor.layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Edit Product'])
            
            @section('page-title', 'Edit Product')
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Edit Product</h4>
                                    <p class="mb-0 text-muted">{{ $product->name }}</p>
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
                                
                                <form action="{{ route('vendor.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="product-form">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <!-- Basic Information -->
                                            <div class="mb-4">
                                                <label for="name" class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4 py-2" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="description" class="form-label fw-bold">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="short_description" class="form-label fw-bold">Short Description</label>
                                                <textarea class="form-control" id="short_description" name="short_description" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                                            </div>
                                            
                                            <!-- Product Type (Read Only) -->
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Product Type</label>
                                                <input type="hidden" name="product_type" value="{{ $product->product_type }}">
                                                <div class="form-control-plaintext">
                                                    <span class="badge bg-{{ $product->product_type === 'simple' ? 'primary' : 'info' }} rounded-pill px-3 py-2">
                                                        {{ ucfirst($product->product_type) }} Product
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            @if($product->product_type === 'simple')
                                            <!-- Pricing (Simple Product) -->
                                            <div id="simple-product-fields" class="{{ $product->product_type === 'simple' ? '' : 'd-none' }}">
                                                <div class="row">
                                                    <div class="col-md-6 mb-4">
                                                        <label for="mrp" class="form-label fw-bold">MRP (₹) <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="mrp" name="mrp" value="{{ old('mrp', $product->mrp) }}" step="0.01" min="0" required>
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        <label for="selling_price" class="form-label fw-bold">Selling Price (₹)</label>
                                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" step="0.01" min="0">
                                                        <div class="form-text">Must be less than or equal to MRP</div>
                                                    </div>
                                                </div>
                                                
                                                <!-- SKU -->
                                                <div class="mb-4">
                                                    <label for="sku" class="form-label fw-bold">SKU</label>
                                                    <input type="text" class="form-control rounded-pill px-4 py-2" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                                                </div>
                                            </div>
                                            
                                            <!-- Stock Status - Only for Simple Products -->
                                            <div class="mb-4 {{ $product->product_type === 'simple' ? '' : 'd-none' }}" id="simple-stock-fields">
                                                <label class="form-label fw-bold">Stock Status</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input type="hidden" name="in_stock" value="0">
                                                    <input class="form-check-input" type="checkbox" id="in_stock" name="in_stock" value="1" {{ old('in_stock', $product->in_stock) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="in_stock">
                                                        <span id="stock-status-text">{{ old('in_stock', $product->in_stock) ? 'In Stock' : 'Out of Stock' }}</span>
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
                                                            <input type="number" class="form-control rounded-pill px-4 py-2" id="low_quantity_threshold" name="low_quantity_threshold" value="{{ old('low_quantity_threshold', $product->low_quantity_threshold) }}" min="0">
                                                            <div class="form-text text-muted">
                                                                <i class="fas fa-bell text-warning me-1"></i>
                                                                You'll receive a notification when stock falls below this quantity
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            <!-- Variable Product Fields -->
                                            <div id="variable-product-fields" class="{{ $product->product_type === 'variable' ? '' : 'd-none' }}">
                                                <!-- Attribute Selection -->
                                                <div class="mb-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="form-label fw-bold mb-0">Product Attributes</label>
                                                    </div>
                                                    <p class="text-muted small">Select attributes for variations (e.g., Size, Color)</p>
                                                    <div id="attribute-selection" class="border rounded-3 p-3">
                                                        @if(isset($attributes) && $attributes->count() > 0)
                                                            @php
                                                                // Get currently used attribute IDs from existing variations
                                                                $usedAttributeIds = [];
                                                                if ($product->variations) {
                                                                    foreach ($product->variations as $variation) {
                                                                        if ($variation->attribute_values) {
                                                                            $attrValues = is_array($variation->attribute_values) ? $variation->attribute_values : json_decode($variation->attribute_values, true);
                                                                            if ($attrValues) {
                                                                                $usedAttributeIds = array_merge($usedAttributeIds, array_keys($attrValues));
                                                                            }
                                                                        }
                                                                    }
                                                                    $usedAttributeIds = array_unique($usedAttributeIds);
                                                                }
                                                            @endphp
                                                            @foreach($attributes as $attribute)
                                                                <div class="form-check mb-2 attribute-item" data-attribute-id="{{ $attribute->id }}">
                                                                    <input class="form-check-input attribute-checkbox" type="checkbox" 
                                                                           id="attribute_{{ $attribute->id }}" 
                                                                           value="{{ $attribute->id }}" 
                                                                           data-attribute-name="{{ $attribute->name }}"
                                                                           data-attribute-values='@json($attribute->values->pluck('value', 'id'))'
                                                                           {{ in_array($attribute->id, $usedAttributeIds) ? 'checked' : '' }}>
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
                                                
                                                <!-- Existing Variations -->
                                                <div class="mb-4">
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
                                                        @if($product->variations && $product->variations->count() > 0)
                                                            @foreach($product->variations as $index => $variation)
                                                                @php
                                                                    $attrValues = is_array($variation->attribute_values) ? $variation->attribute_values : json_decode($variation->attribute_values, true);
                                                                    $variationName = [];
                                                                    $attributeBadges = '';
                                                                    if ($attrValues) {
                                                                        foreach ($attrValues as $attrId => $valueId) {
                                                                            $attr = $attributes->firstWhere('id', $attrId);
                                                                            if ($attr) {
                                                                                $value = $attr->values->firstWhere('id', $valueId);
                                                                                if ($value) {
                                                                                    $variationName[] = $value->value;
                                                                                    $attributeBadges .= '<span class="badge bg-light text-dark border me-1">' . $attr->name . ': ' . $value->value . '</span>';
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                    $variationNameStr = implode(' - ', $variationName) ?: 'Variation ' . ($index + 1);
                                                                @endphp
                                                                <div class="accordion-item variation-card" data-variation-index="{{ $index }}" data-variation-id="{{ $variation->id }}">
                                                                    <h2 class="accordion-header" id="heading-{{ $index }}">
                                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}" aria-expanded="false" aria-controls="collapse-{{ $index }}">
                                                                            <div class="d-flex align-items-center w-100 me-3">
                                                                                <div class="variation-header-image me-3">
                                                                                    @if($variation->image)
                                                                                        <img src="{{ $variation->image_url }}" alt="{{ $variationNameStr }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                                                    @else
                                                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                                            <i class="fas fa-image text-muted"></i>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="flex-grow-1">
                                                                                    <strong class="text-primary">{{ $variationNameStr }}</strong>
                                                                                    <div class="small text-muted">
                                                                                        {!! $attributeBadges ?: 'No attributes' !!}
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
                                                                                        <input type="hidden" name="variations[{{ $index }}][id]" value="{{ $variation->id }}">
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
                                                                                        <!-- Hidden Inputs for Attributes -->
                                                                                        <div class="col-12">
                                                                                            @if($attrValues)
                                                                                                @foreach($attrValues as $attrId => $valueId)
                                                                                                    <input type="hidden" name="variations[{{ $index }}][attribute_values][{{ $attrId }}]" value="{{ $valueId }}">
                                                                                                @endforeach
                                                                                            @endif
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
                                                                                        <label class="form-label small fw-bold">Stock <span class="text-danger">*</span></label>
                                                                                        <input type="number" class="form-control form-control-sm" 
                                                                                               name="variations[{{ $index }}][stock_quantity]" 
                                                                                               value="{{ $variation->stock_quantity }}" 
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
                                                                                                   value="1" {{ $variation->in_stock ? 'checked' : '' }}>
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
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    
                                                    @if(!$product->variations || $product->variations->count() == 0)
                                                        <p class="text-muted text-center mb-0" id="no-variations-message">No variations added yet. Select attributes and click "Auto-Generate All" or "Add Manual".</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
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
                                                                    <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                                                    <option value="published" {{ old('status', $product->status) == 'published' ? 'selected' : '' }}>Published</option>
                                                                </select>
                                                            </div>
                                                            <button type="submit" class="btn btn-theme w-100 rounded-pill">
                                                                <i class="fas fa-save me-2"></i>Update Product
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
                                                                @php
                                                                    // Get product categories from product_categories JSON field
                                                                    $productCategoriesData = is_array($product->product_categories) ? $product->product_categories : json_decode($product->product_categories ?? '[]', true);
                                                                    $productCategoryIds = [];
                                                                    $productSubcategoryIds = [];
                                                                    if (is_array($productCategoriesData)) {
                                                                        foreach ($productCategoriesData as $catData) {
                                                                            if (isset($catData['category_id'])) {
                                                                                $productCategoryIds[] = $catData['category_id'];
                                                                            }
                                                                            if (isset($catData['subcategory_ids']) && is_array($catData['subcategory_ids'])) {
                                                                                $productSubcategoryIds = array_merge($productSubcategoryIds, $catData['subcategory_ids']);
                                                                            }
                                                                        }
                                                                    }
                                                                @endphp
                                                                @foreach($categories ?? [] as $category)
                                                                    @php
                                                                        $isCategoryChecked = in_array($category->id, $productCategoryIds);
                                                                    @endphp
                                                                    <div class="form-check mb-2 category-item" data-category-id="{{ $category->id }}">
                                                                        <input class="form-check-input category-checkbox" type="checkbox" id="cat_{{ $category->id }}" value="{{ $category->id }}" name="product_categories[{{ $category->id }}][category_id]" {{ $isCategoryChecked ? 'checked' : '' }}>
                                                                        <label class="form-check-label fw-bold" for="cat_{{ $category->id }}">{{ $category->name }}</label>
                                                                        @if($category->subCategories && $category->subCategories->count() > 0)
                                                                            <div class="subcategory-container ms-4 mt-2 {{ $isCategoryChecked ? '' : 'd-none' }}" id="subcategory_container_{{ $category->id }}">
                                                                                @foreach($category->subCategories as $subcategory)
                                                                                    <div class="form-check mb-1">
                                                                                        <input class="form-check-input subcategory-checkbox" type="checkbox" id="subcat_{{ $subcategory->id }}" value="{{ $subcategory->id }}" name="product_categories[{{ $category->id }}][subcategory_ids][]" data-category-id="{{ $category->id }}" {{ in_array($subcategory->id, $productSubcategoryIds) ? 'checked' : '' }}>
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
                                                        @if($product->mainPhoto)
                                                            <div class="position-relative">
                                                                <img src="{{ $product->mainPhoto->url }}" class="img-fluid rounded" alt="{{ $product->name }}" style="max-height: 200px; object-fit: contain; width: 100%;">
                                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeMainImage()">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        @else
                                                            <div class="upload-area" id="main-image-upload-area" style="min-height: 180px; border: 2px dashed #dee2e6; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                                                <div class="text-center">
                                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                                    <p class="text-muted mb-2 small">Click to upload an image</p>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <input type="file" class="form-control d-none" id="main_photo" name="main_photo" accept="image/*">
                                                    <input type="hidden" name="remove_main_photo" id="remove_main_photo" value="0">
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
                                                        @if($product->gallery_photos && count($product->gallery_photos) > 0)
                                                            @foreach($product->gallery_photos as $index => $photo)
                                                                <div class="col-4 col-md-6 mb-2" data-gallery-index="{{ $index }}" data-gallery-path="{{ $photo['path'] }}">
                                                                    <div class="position-relative">
                                                                        <img src="{{ $photo['url'] }}" class="img-fluid rounded" alt="Gallery" style="height: 80px; width: 100%; object-fit: cover;" onerror="this.style.display='none';">
                                                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle" style="width: 24px; height: 24px; padding: 0; font-size: 10px;" onclick="removeGalleryImage({{ $index }})">
                                                                            <i class="fas fa-times"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="col-12 text-center text-muted py-3" id="gallery-empty-state">
                                                                <i class="fas fa-images fa-2x mb-2"></i>
                                                                <p class="small mb-0">No gallery images selected</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <input type="file" class="form-control" id="gallery_images" name="gallery_images[]" accept="image/*" multiple>
                                                    <!-- Hidden input to preserve existing gallery images -->
                                                    @php
                                                        $existingGalleryPaths = $product->product_gallery ?? [];
                                                    @endphp
                                                    <input type="hidden" name="existing_gallery" id="existing_gallery" value="{{ json_encode($existingGalleryPaths) }}">
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
    let variationCounter = {{ $product->variations ? $product->variations->count() : 0 }};
    
    $(document).ready(function() {
        // Initialize selected attributes from checked checkboxes
        updateSelectedAttributes();
        
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
        
        // Generate All Variations
        $('#generate-variations-btn').on('click', function() {
            if (selectedAttributes.length === 0) {
                alert('Please select at least one attribute first.');
                return;
            }
            
            // Generate all combinations
            const combinations = generateCombinations(selectedAttributes);
            
            // Add each combination as a row (skip duplicates)
            let addedCount = 0;
            combinations.forEach(combination => {
                const beforeCount = $('#variationsAccordion .accordion-item').length;
                addVariationRow(combination);
                const afterCount = $('#variationsAccordion .accordion-item').length;
                if (afterCount > beforeCount) {
                    addedCount++;
                }
            });
            
            // Hide no variations message if variations were added
            if ($('#variationsAccordion .accordion-item').length > 0) {
                $('#no-variations-message').hide();
            }
            
            if (addedCount > 0) {
                alert(`Successfully generated ${addedCount} new variation(s)!`);
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
                <div class="accordion-item variation-card" data-variation-index="${index}" data-variation-id="">
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
                                        ${attributeBadges || 'No attributes'}
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
                                    <input type="hidden" name="variations[${index}][image_id]" class="variation-image-id" data-variation-index="${index}">
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#variationsAccordion').append(card);
            $('#no-variations-message').hide();
        });
        
        // Remove variation
        $(document).on('click', '.remove-variation-btn', function() {
            $(this).closest('.variation-card').remove();
            
            // Show no variations message if no variations left
            if ($('#variationsAccordion .variation-card').length === 0) {
                $('#no-variations-message').show();
            }
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
            const productType = $('input[name="product_type"]').val();
            
            if (productType === 'variable') {
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
        
        // Main photo upload handler
        $(document).on('change', '#main_photo', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                $('#remove_main_photo').val('0'); // Reset removal flag
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#main-image-preview').html(`
                        <div class="position-relative">
                            <img src="${e.target.result}" class="img-fluid rounded" alt="Main Photo" style="max-height: 200px; object-fit: contain; width: 100%;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeMainImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Gallery images upload handler
        $(document).on('change', '#gallery_images', function(e) {
            const files = Array.from(e.target.files);
            const $galleryPreview = $('#gallery-preview');
            
            // Remove empty state if exists
            $('#gallery-empty-state').remove();
            
            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageHtml = `
                            <div class="col-4 col-md-6 mb-2 new-gallery-image">
                                <div class="position-relative">
                                    <img src="${e.target.result}" class="img-fluid rounded" alt="Gallery" style="height: 80px; width: 100%; object-fit: cover;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle" style="width: 24px; height: 24px; padding: 0; font-size: 10px;" onclick="$(this).closest('.col-4').remove();">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        $galleryPreview.append(imageHtml);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
        
        // Click handler for main image upload area
        $(document).on('click', '#main-image-upload-area', function() {
            $('#main_photo').click();
        });
    });
    
    // Remove main image
    function removeMainImage() {
        $('#main_photo').val('');
        $('#remove_main_photo').val('1'); // Mark for removal
        $('#main-image-preview').html(`
            <div class="upload-area" id="main-image-upload-area" style="min-height: 180px; border: 2px dashed #dee2e6; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div class="text-center">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2 small">Click to upload an image</p>
                </div>
            </div>
        `);
    }
    
    // Remove gallery image (for existing images)
    function removeGalleryImage(index) {
        const $imageElement = $('[data-gallery-index="' + index + '"]');
        const imagePath = $imageElement.data('gallery-path');
        
        // Remove from DOM
        $imageElement.remove();
        
        // Update the hidden input to remove this image path
        const existingGallery = JSON.parse($('#existing_gallery').val() || '[]');
        const updatedGallery = existingGallery.filter(path => path !== imagePath);
        $('#existing_gallery').val(JSON.stringify(updatedGallery));
        
        // Show empty state if no images left
        if ($('#gallery-preview').children().length === 0) {
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
