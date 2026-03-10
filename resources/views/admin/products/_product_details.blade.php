<div class="mb-4">
    <h5 class="fw-bold mb-3">Basic Information</h5>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Product Name</label>
            <div class="fw-medium">{{ $product->name }}</div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Product Type</label>
            <div>
                @if($product->isVariable())
                    <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3 py-2">
                        <i class="fas fa-layer-group me-1"></i> Variable Product
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                        <i class="fas fa-box me-1"></i> Simple Product
                    </span>
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Status</label>
            <div>
                @if($product->status === 'published')
                    <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                        Published
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                        Draft
                    </span>
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">MRP</label>
            <div class="fw-medium">
                @if($product->isVariable())
                    @php
                        $mrpRange = $product->mrp_range;
                    @endphp
                    @if($mrpRange['min'] == $mrpRange['max'])
                        ₹{{ number_format($mrpRange['min'], 2) }}
                    @else
                        ₹{{ number_format($mrpRange['min'], 2) }} - ₹{{ number_format($mrpRange['max'], 2) }}
                    @endif
                @else
                    ₹{{ number_format($product->mrp, 2) }}
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Selling Price</label>
            <div class="fw-medium">
                @if($product->isVariable())
                    @php
                        $priceRange = $product->price_range;
                    @endphp
                    @if($priceRange['min'] == $priceRange['max'])
                        ₹{{ number_format($priceRange['min'], 2) }}
                    @else
                        ₹{{ number_format($priceRange['min'], 2) }} - ₹{{ number_format($priceRange['max'], 2) }}
                    @endif
                @else
                    ₹{{ number_format($product->selling_price ?? 0, 2) }}
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Stock Status</label>
            <div>
                @php
                    $totalStock = $product->isVariable() ? $product->total_stock : $product->stock_quantity;
                    $isInStock = $product->isVariable() 
                        ? $product->variations()->where('in_stock', true)->exists()
                        : $product->in_stock;
                @endphp
                
                @if($isInStock && $totalStock > 0)
                    @if($product->isLowStock())
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">
                            <i class="fas fa-exclamation-triangle me-1"></i> Low Stock ({{ $totalStock }})
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                            <i class="fas fa-check-circle me-1"></i> In Stock ({{ $totalStock }})
                        </span>
                    @endif
                @elseif($totalStock == 0)
                    <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill px-3 py-2">
                        <i class="fas fa-times-circle me-1"></i> Out of Stock
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                        <i class="fas fa-ban me-1"></i> Not Available
                    </span>
                @endif
                
                @if($product->isVariable())
                    <div class="small text-muted mt-1">
                        Total from {{ $product->variations()->count() }} variation(s)
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Category</label>
            <div class="fw-medium">
                @if($product->category)
                    {{ $product->category->name }}
                @else
                    <span class="text-muted">No category assigned</span>
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Created</label>
            <div class="fw-medium">
                @if($product->created_at)
                    {{ $product->created_at->format('F j, Y \a\t g:i A') }}
                @else
                    N/A
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-bold mb-3">Description</h5>
    <div class="border rounded-3 p-3">
        @if($product->description)
            {!! $product->description !!}
        @else
            <span class="text-muted">No description provided</span>
        @endif
    </div>
</div>

@if($product->isVariable() && $product->variations()->count() > 0)
<div class="mb-4">
    <h5 class="fw-bold mb-3">Product Variations ({{ $product->variations()->count() }})</h5>
    <div class="border rounded-3 p-3">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Variation</th>
                        <th>MRP</th>
                        <th>Selling Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product->variations as $variation)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($variation->image)
                                        <img src="{{ asset('storage/' . $variation->image) }}" 
                                             class="rounded me-2" width="30" height="30" 
                                             alt="Variation" 
                                             onerror="this.style.display='none';">
                                    @endif
                                    <div>
                                        @if($variation->attribute_values)
                                            @foreach($variation->attribute_values as $attrId => $valueId)
                                                @php
                                                    $attribute = \App\Models\ProductAttribute::find($attrId);
                                                    $value = $attribute ? $attribute->values()->find($valueId) : null;
                                                @endphp
                                                @if($attribute && $value)
                                                    <span class="badge bg-light text-dark border me-1">
                                                        {{ $attribute->name }}: {{ $value->value }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        @endif
                                        @if($variation->is_default)
                                            <span class="badge bg-primary-subtle text-primary-emphasis ms-1">Default</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>₹{{ number_format($variation->mrp ?? 0, 2) }}</td>
                            <td>₹{{ number_format($variation->selling_price ?? 0, 2) }}</td>
                            <td>
                                @if($variation->in_stock && $variation->stock_quantity > 0)
                                    <span class="badge bg-success-subtle text-success-emphasis">
                                        {{ $variation->stock_quantity }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger-emphasis">
                                        Out of Stock
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($variation->in_stock)
                                    <i class="fas fa-check-circle text-success" title="In Stock"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger" title="Out of Stock"></i>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total Stock:</td>
                        <td colspan="2">
                            <span class="badge bg-primary-subtle text-primary-emphasis">
                                {{ $product->total_stock }} units
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif

<div class="mb-4">
    <h5 class="fw-bold mb-3">Main Photo</h5>
    <div class="border rounded-3 p-3 text-center">
        @if($product->mainPhoto)
            <img src="{{ $product->mainPhoto->url }}" class="img-fluid rounded" alt="{{ $product->name }}" style="max-height: 150px; object-fit: contain;">
        @else
            <div class="py-5">
                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No main photo</p>
            </div>
        @endif
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-bold mb-3">Gallery Photos</h5>
    <div class="border rounded-3 p-3">
        @if($product->gallery_photos && count($product->gallery_photos) > 0)
            <div class="d-flex flex-wrap gap-2">
                @foreach($product->gallery_photos as $photo)
                    <div>
                        <img src="{{ $photo['url'] }}" class="img-fluid rounded" alt="Gallery image" style="height: 80px; object-fit: cover;" onerror="this.style.display='none';">
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-3 text-center">
                <i class="fas fa-images fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">No gallery photos</p>
            </div>
        @endif
    </div>
</div>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">SEO Settings</h5>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" id="toggle-seo-settings-modal">
            <i class="fas fa-chevron-down me-1"></i> <span>Expand</span>
        </button>
    </div>
    <div id="seo-settings-content-modal" class="border rounded-3 p-3 d-none">
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="text-muted small mb-1">Meta Title</label>
                <div class="fw-medium">
                    @if($product->meta_title)
                        {{ $product->meta_title }}
                    @else
                        <span class="text-muted">Not set</span>
                    @endif
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <label class="text-muted small mb-1">Meta Description</label>
                <div class="fw-medium">
                    @if($product->meta_description)
                        {{ $product->meta_description }}
                    @else
                        <span class="text-muted">Not set</span>
                    @endif
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <label class="text-muted small mb-1">Meta Keywords</label>
                <div class="fw-medium">
                    @if($product->meta_keywords)
                        {{ $product->meta_keywords }}
                    @else
                        <span class="text-muted">Not set</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-4">
    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-theme rounded-pill px-4 me-2">
        <i class="fas fa-edit me-2"></i> Edit Product
    </a>
    
    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger rounded-pill px-4">
            <i class="fas fa-trash me-2"></i> Delete Product
        </button>
    </form>
</div>