@extends('vendor.layouts.app')

@section('title', 'Low Stock Products')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Low Stock Products'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                        Low Stock Products
                                    </h4>
                                    <p class="mb-0 text-muted">Products with stock quantity below their threshold</p>
                                </div>
                                <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Products
                                </a>
                            </div>
                            
                            <div class="card-body">
                                @if($paginatedProducts->isEmpty())
                                    <div class="text-center py-5">
                                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                                        <h5 class="text-muted">All products have sufficient stock!</h5>
                                        <p class="text-muted">No products are currently below their low stock threshold.</p>
                                    </div>
                                @else
                                    <div class="alert alert-warning rounded-3 mb-4">
                                        <i class="fas fa-bell me-2"></i>
                                        <strong>{{ $paginatedProducts->total() }} product(s)</strong> have stock levels below their threshold. Consider restocking soon!
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Product</th>
                                                    <th>Type</th>
                                                    <th>Current Stock</th>
                                                    <th>Threshold</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($paginatedProducts as $product)
                                                    @php
                                                        // Determine stock and threshold based on product type
                                                        if ($product->isVariable()) {
                                                            $totalStock = $product->total_stock;
                                                            $minThreshold = $product->variations->min('low_quantity_threshold') ?? $product->low_quantity_threshold ?? 10;
                                                            $productType = 'Variable';
                                                            $productTypeBadge = 'bg-info-subtle text-info-emphasis';
                                                        } else {
                                                            $totalStock = $product->stock_quantity;
                                                            $minThreshold = $product->low_quantity_threshold ?? 10;
                                                            $productType = 'Simple';
                                                            $productTypeBadge = 'bg-primary-subtle text-primary-emphasis';
                                                        }
                                                        
                                                        // Calculate percentage
                                                        $percentage = $minThreshold > 0 ? ($totalStock / $minThreshold) * 100 : 0;
                                                        
                                                        // Determine badge color based on stock level
                                                        if ($totalStock == 0) {
                                                            $stockBadgeClass = 'bg-danger text-white';
                                                            $stockIcon = 'fa-times-circle';
                                                        } elseif ($totalStock <= $minThreshold) {
                                                            $stockBadgeClass = 'bg-warning text-dark';
                                                            $stockIcon = 'fa-exclamation-triangle';
                                                        } else {
                                                            $stockBadgeClass = 'bg-success text-white';
                                                            $stockIcon = 'fa-check-circle';
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td class="fw-bold">{{ $product->id }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                @if($product->mainPhoto)
                                                                    <img src="{{ $product->mainPhoto->url }}" 
                                                                         class="rounded me-3" width="40" height="40" alt="{{ $product->name }}" 
                                                                         style="object-fit: cover;"
                                                                         loading="lazy">
                                                                @else
                                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                        <i class="fas fa-image text-muted"></i>
                                                                    </div>
                                                                @endif
                                                                <div>
                                                                    <div class="fw-medium">{{ $product->name }}</div>
                                                                    @if($product->isVariable())
                                                                        <small class="text-muted">{{ $product->variations->count() }} variations</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $productTypeBadge }} rounded-pill px-2 py-1">
                                                                {{ $productType }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $stockBadgeClass }} rounded-pill px-3 py-2 fs-6">
                                                                <i class="fas {{ $stockIcon }} me-1"></i>
                                                                {{ $totalStock }}
                                                            </span>
                                                            @if($product->isVariable() && $totalStock == 0)
                                                                <div class="small text-danger mt-1">
                                                                    <i class="fas fa-info-circle"></i> All variations out of stock
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                                                                {{ $minThreshold }}
                                                            </span>
                                                            @if($product->isVariable())
                                                                <div class="small text-muted mt-1">
                                                                    <i class="fas fa-info-circle"></i> Min threshold
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="progress" style="height: 8px; width: 100px;">
                                                                <div class="progress-bar {{ $percentage < 50 ? 'bg-danger' : ($percentage < 100 ? 'bg-warning' : 'bg-success') }}" 
                                                                     role="progressbar" 
                                                                     style="width: {{ min($percentage, 100) }}%">
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">{{ round($percentage) }}% of threshold</small>
                                                            
                                                            @if($product->isVariable())
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#variations-{{ $product->id }}">
                                                                        <i class="fas fa-list"></i> View Variations
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                                <i class="fas fa-edit me-1"></i> Update Stock
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    
                                                    @if($product->isVariable())
                                                        <tr class="collapse" id="variations-{{ $product->id }}">
                                                            <td colspan="7" class="bg-light">
                                                                <div class="p-3">
                                                                    <h6 class="mb-3"><i class="fas fa-layer-group me-2"></i>Variation Stock Details</h6>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered mb-0">
                                                                            <thead class="table-secondary">
                                                                                <tr>
                                                                                    <th>Variation</th>
                                                                                    <th>SKU</th>
                                                                                    <th>Stock</th>
                                                                                    <th>Threshold</th>
                                                                                    <th>Status</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($product->variations as $variation)
                                                                                    @php
                                                                                        $varThreshold = $variation->low_quantity_threshold ?? $product->low_quantity_threshold ?? 10;
                                                                                        $varStock = $variation->stock_quantity;
                                                                                        
                                                                                        if ($varStock == 0) {
                                                                                            $varBadge = 'bg-danger text-white';
                                                                                            $varStatus = 'Out of Stock';
                                                                                            $varIcon = 'fa-times-circle';
                                                                                        } elseif ($varStock <= $varThreshold) {
                                                                                            $varBadge = 'bg-warning text-dark';
                                                                                            $varStatus = 'Low Stock';
                                                                                            $varIcon = 'fa-exclamation-triangle';
                                                                                        } else {
                                                                                            $varBadge = 'bg-success text-white';
                                                                                            $varStatus = 'In Stock';
                                                                                            $varIcon = 'fa-check-circle';
                                                                                        }
                                                                                    @endphp
                                                                                    <tr>
                                                                                        <td>
                                                                                            <strong>{{ $variation->name }}</strong>
                                                                                            @if($variation->formatted_attributes)
                                                                                                <br>
                                                                                                @foreach($variation->formatted_attributes as $attr => $value)
                                                                                                    <span class="badge bg-light text-dark border me-1">{{ $attr }}: {{ $value }}</span>
                                                                                                @endforeach
                                                                                            @endif
                                                                                        </td>
                                                                                        <td><code>{{ $variation->sku ?? 'N/A' }}</code></td>
                                                                                        <td>
                                                                                            <span class="badge {{ $varBadge }} rounded-pill">
                                                                                                {{ $varStock }}
                                                                                            </span>
                                                                                        </td>
                                                                                        <td>{{ $varThreshold }}</td>
                                                                                        <td>
                                                                                            <span class="badge {{ $varBadge }} rounded-pill">
                                                                                                <i class="fas {{ $varIcon }} me-1"></i>
                                                                                                {{ $varStatus }}
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $paginatedProducts->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
