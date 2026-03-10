@extends('vendor.layouts.app')

@section('title', 'Products')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Product Management'])
            
            @section('page-title', 'Products')
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Product Management</h4>
                                        <p class="mb-0 text-muted small">Manage your store products</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if(isset($lowStockCount) && $lowStockCount > 0)
                                        <a href="{{ route('vendor.products.low-stock') }}" class="btn btn-sm btn-warning rounded-pill px-3">
                                            <i class="fas fa-exclamation-triangle me-1"></i><span class="d-none d-sm-inline">Low Stock</span> 
                                            <span class="badge bg-danger ms-1">{{ $lowStockCount }}</span>
                                        </a>
                                        @endif
                                        <a href="{{ route('vendor.products.create') }}" class="btn btn-sm btn-theme rounded-pill px-3">
                                            <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline">Add Product</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="productsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>SR No.</th>
                                                <th>Product</th>
                                                <th>MRP</th>
                                                <th>Selling Price</th>
                                                <th>Stock</th>
                                                <th>Status</th>
                                                <th>Featured</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($products as $index => $product)
                                                <tr>
                                                    <td class="fw-bold">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($product->mainPhoto)
                                                                <img src="{{ $product->mainPhoto->url }}" 
                                                                     class="rounded me-3" width="40" height="40" alt="{{ $product->name }}" 
                                                                     loading="lazy">
                                                            @else
                                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <div class="fw-medium">
                                                                    {{ Str::limit($product->name, 30) }}
                                                                    @if($product->isVariable())
                                                                        <span class="badge bg-info-subtle text-info-emphasis ms-1" title="Variable Product with {{ $product->variations()->count() }} variations">
                                                                            <i class="fas fa-layer-group"></i>
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                @if($product->sku)
                                                                    <small class="text-muted">SKU: {{ $product->sku }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($product->isVariable())
                                                            @php
                                                                $mrpRange = $product->mrp_range;
                                                            @endphp
                                                            @if($mrpRange['min'] == $mrpRange['max'])
                                                                ₹{{ number_format($mrpRange['min'], 2) }}
                                                            @else
                                                                <span title="MRP Range">
                                                                    ₹{{ number_format($mrpRange['min'], 2) }} - ₹{{ number_format($mrpRange['max'], 2) }}
                                                                </span>
                                                            @endif
                                                        @else
                                                            ₹{{ number_format($product->mrp, 2) }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($product->isVariable())
                                                            @php
                                                                $priceRange = $product->price_range;
                                                            @endphp
                                                            @if($priceRange['min'] == $priceRange['max'])
                                                                ₹{{ number_format($priceRange['min'], 2) }}
                                                            @else
                                                                <span title="Price Range">
                                                                    ₹{{ number_format($priceRange['min'], 2) }} - ₹{{ number_format($priceRange['max'], 2) }}
                                                                </span>
                                                            @endif
                                                        @else
                                                            ₹{{ number_format($product->selling_price ?? 0, 2) }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $totalStock = $product->isVariable() ? $product->total_stock : $product->stock_quantity;
                                                            $isInStock = $product->isVariable() 
                                                                ? $product->variations()->where('in_stock', true)->exists()
                                                                : $product->in_stock;
                                                        @endphp
                                                        
                                                        @if($isInStock && $totalStock > 0)
                                                            @if($product->isLowStock())
                                                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">
                                                                    <i class="fas fa-exclamation-triangle me-1"></i> Low ({{ $totalStock }})
                                                                </span>
                                                            @else
                                                                <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                                                    <i class="fas fa-check-circle me-1"></i> {{ $totalStock }}
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill px-3 py-2">
                                                                <i class="fas fa-times-circle me-1"></i> Out
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($product->status === 'published')
                                                            <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                                                                Published
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                                                                Draft
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input featured-toggle" 
                                                                   type="checkbox" 
                                                                   role="switch" 
                                                                   id="featured-{{ $product->id }}"
                                                                   data-product-id="{{ $product->id }}"
                                                                   {{ $product->is_featured ? 'checked' : '' }}
                                                                   style="cursor: pointer; width: 2.5rem; height: 1.25rem;">
                                                            <label class="form-check-label" for="featured-{{ $product->id }}" style="cursor: pointer;">
                                                                <span class="badge {{ $product->is_featured ? 'bg-warning' : 'bg-secondary' }} featured-badge-{{ $product->id }}">
                                                                    <i class="fas fa-star"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="{{ route('vendor.products.show', $product) }}" class="btn btn-outline-info rounded-start-pill px-3">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-outline-primary px-3">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('vendor.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger rounded-end-pill px-3">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-5">
                                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted mb-3">No products found</p>
                                                        <a href="{{ route('vendor.products.create') }}" class="btn btn-theme rounded-pill">
                                                            <i class="fas fa-plus me-2"></i>Add Your First Product
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                @if($products->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $products->links() }}
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

@section('scripts')
<script>
    $(document).ready(function() {
        // Set up AJAX to include CSRF token in all requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        @if($products->count() > 0)
        $('#productsTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "ordering": true,
            "searching": true,
            "info": true,
            "paging": true,
            "columnDefs": [
                { "orderable": false, "targets": [6, 7] } // Disable sorting on Featured and Actions columns
            ],
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ products",
                "infoEmpty": "Showing 0 to 0 of 0 products",
                "infoFiltered": "(filtered from _MAX_ total products)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                },
                "emptyTable": "No products available"
            },
            "drawCallback": function(settings) {
                // Reinitialize tooltips after each draw
                $('[data-bs-toggle="tooltip"]').tooltip();
                
                // Reattach featured toggle event handlers
                attachFeaturedToggleHandlers();
            }
        });
        // Adjust select width after DataTable initializes
        $('.dataTables_length select').css('width', '80px');
        @endif
        
        // Attach featured toggle handlers
        attachFeaturedToggleHandlers();
    });
    
    function attachFeaturedToggleHandlers() {
        $('.featured-toggle').off('change').on('change', function() {
            const checkbox = $(this);
            const productId = checkbox.data('product-id');
            const isChecked = checkbox.prop('checked');
            
            console.log('Toggle featured for product:', productId, 'New state:', isChecked);
            
            // Disable checkbox during request
            checkbox.prop('disabled', true);
            
            $.ajax({
                url: `/vendor/products/${productId}/toggle-featured`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                beforeSend: function() {
                    console.log('Sending request to:', `/vendor/products/${productId}/toggle-featured`);
                },
                success: function(response) {
                    console.log('Success response:', response);
                    if (response.success) {
                        // Update badge color
                        const badge = $(`.featured-badge-${productId}`);
                        if (response.is_featured) {
                            badge.removeClass('bg-secondary').addClass('bg-warning');
                        } else {
                            badge.removeClass('bg-warning').addClass('bg-secondary');
                        }
                        
                        // Show success message
                        showToast('success', response.message);
                    } else {
                        // Revert checkbox state
                        checkbox.prop('checked', !isChecked);
                        showToast('error', response.message || 'Failed to update featured status');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    
                    // Revert checkbox state
                    checkbox.prop('checked', !isChecked);
                    
                    let errorMessage = 'An error occurred while updating featured status';
                    
                    // Try to parse error response
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMessage = 'Route not found. Please refresh the page.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to perform this action.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please try again later.';
                    } else if (xhr.status === 419) {
                        errorMessage = 'Session expired. Please refresh the page.';
                    }
                    
                    showToast('error', errorMessage);
                },
                complete: function() {
                    // Re-enable checkbox
                    checkbox.prop('disabled', false);
                }
            });
        });
    }
    
    function showToast(type, message) {
        // Create toast element
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Append to body or toast container
        let toastContainer = $('#toast-container');
        if (toastContainer.length === 0) {
            $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
            toastContainer = $('#toast-container');
        }
        
        const toastElement = $(toastHtml);
        toastContainer.append(toastElement);
        
        // Initialize and show toast
        const toast = new bootstrap.Toast(toastElement[0], {
            autohide: true,
            delay: 3000
        });
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
</script>
@endsection
