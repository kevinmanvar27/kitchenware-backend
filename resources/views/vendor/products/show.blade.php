@extends('vendor.layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Product Details'])
            
            @section('page-title', 'Product Details')
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">{{ $product->name }}</h4>
                                    <p class="mb-0 text-muted">Product ID: #{{ $product->id }}</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-primary rounded-pill px-4">
                                        <i class="fas fa-edit me-2"></i> Edit
                                    </a>
                                    <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                        <i class="fas fa-arrow-left me-2"></i> Back
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-5 mb-4">
                                        <!-- Product Images -->
                                        @if($product->mainPhoto)
                                            <img src="{{ $product->mainPhoto->url }}" alt="{{ $product->name }}" class="img-fluid rounded mb-3">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                                <i class="fas fa-image fa-4x text-muted"></i>
                                            </div>
                                        @endif
                                        
                                        @if($product->gallery_photos && count($product->gallery_photos) > 0)
                                            <div class="row g-2">
                                                @foreach($product->gallery_photos as $photo)
                                                    <div class="col-3">
                                                        <img src="{{ $photo['url'] }}" alt="Gallery" class="img-fluid rounded" onerror="this.style.display='none';">
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="col-lg-7">
                                        <!-- Product Info -->
                                        <div class="mb-4">
                                            <span class="badge bg-{{ $product->status === 'published' ? 'success' : 'secondary' }} rounded-pill px-3 py-2 me-2">
                                                {{ ucfirst($product->status) }}
                                            </span>
                                            <span class="badge bg-{{ $product->product_type === 'simple' ? 'primary' : 'info' }} rounded-pill px-3 py-2">
                                                {{ ucfirst($product->product_type) }} Product
                                            </span>
                                        </div>
                                        
                                        @if($product->product_type === 'simple')
                                        <div class="row mb-4">
                                            <div class="col-6">
                                                <h6 class="text-muted mb-1">MRP</h6>
                                                <h4 class="text-decoration-line-through text-muted">₹{{ number_format($product->mrp, 2) }}</h4>
                                            </div>
                                            <div class="col-6">
                                                <h6 class="text-muted mb-1">Selling Price</h6>
                                                <h4 class="text-success">₹{{ number_format($product->selling_price ?? $product->mrp, 2) }}</h4>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        <div class="table-responsive mb-4">
                                            <table class="table table-borderless">
                                                <tbody>
                                                    @if($product->sku)
                                                    <tr>
                                                        <td class="text-muted" style="width: 150px;">SKU</td>
                                                        <td class="fw-medium">{{ $product->sku }}</td>
                                                    </tr>
                                                    @endif
                                                    @if($product->product_type === 'simple')
                                                    <tr>
                                                        <td class="text-muted">Stock Status</td>
                                                        <td>
                                                            @if($product->in_stock && $product->stock_quantity > 0)
                                                                @if($product->isLowStock())
                                                                    <span class="badge bg-warning">Low Stock ({{ $product->stock_quantity }})</span>
                                                                @else
                                                                    <span class="badge bg-success">In Stock ({{ $product->stock_quantity }})</span>
                                                                @endif
                                                            @else
                                                                <span class="badge bg-danger">Out of Stock</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Low Stock Alert</td>
                                                        <td class="fw-medium">{{ $product->low_quantity_threshold ?? 10 }} units</td>
                                                    </tr>
                                                    @endif
                                                    <tr>
                                                        <td class="text-muted">Categories</td>
                                                        <td>
                                                            @forelse($product->categories as $category)
                                                                <span class="badge bg-light text-dark me-1">{{ $category->name }}</span>
                                                            @empty
                                                                <span class="text-muted">No categories</span>
                                                            @endforelse
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Created</td>
                                                        <td class="fw-medium">{{ $product->created_at->format('M d, Y H:i') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Last Updated</td>
                                                        <td class="fw-medium">{{ $product->updated_at->format('M d, Y H:i') }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        @if($product->short_description)
                                        <div class="mb-4">
                                            <h6 class="fw-bold">Short Description</h6>
                                            <p class="text-muted">{{ $product->short_description }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($product->description)
                                <div class="mt-4 pt-4 border-top">
                                    <h6 class="fw-bold mb-3">Full Description</h6>
                                    <div class="text-muted">{!! nl2br(e($product->description)) !!}</div>
                                </div>
                                @endif
                                
                                @if($product->product_type === 'variable' && $product->variations && $product->variations->count() > 0)
                                <div class="mt-4 pt-4 border-top">
                                    <h6 class="fw-bold mb-3">Product Variations</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Variation</th>
                                                    <th>SKU</th>
                                                    <th>MRP</th>
                                                    <th>Selling Price</th>
                                                    <th>Stock</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->variations as $variation)
                                                <tr>
                                                    <td class="fw-medium">{{ $variation->name ?? 'Variation' }}</td>
                                                    <td>{{ $variation->sku ?? '-' }}</td>
                                                    <td>₹{{ number_format($variation->mrp, 2) }}</td>
                                                    <td>₹{{ number_format($variation->selling_price, 2) }}</td>
                                                    <td>{{ $variation->stock_quantity }}</td>
                                                    <td>
                                                        @if($variation->in_stock)
                                                            <span class="badge bg-success">In Stock</span>
                                                        @else
                                                            <span class="badge bg-danger">Out of Stock</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
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
