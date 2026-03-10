@extends('vendor.layouts.app')

@section('title', 'Create Invoice')

@push('styles')
<style>
    .product-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
    }
    .product-item:hover {
        background: #e9ecef;
    }
</style>
@endpush

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Create Invoice'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('vendor.invoices.index') }}" class="text-decoration-none">
                                <i class="fas fa-file-invoice me-1"></i>Invoices
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>

                <form action="{{ route('vendor.invoices.store') }}" method="POST" id="invoiceForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Customer Selection -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-user me-2"></i>Customer (Optional)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <select name="user_id" class="form-select rounded-pill">
                                        <option value="">Select Customer (Guest Invoice)</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->email }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Products -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-box me-2"></i>Products
                                    </h5>
                                    <button type="button" class="btn btn-theme rounded-pill btn-sm" id="addProductBtn">
                                        <i class="fas fa-plus me-1"></i> Add Product
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="productsList">
                                        <!-- Products will be added here -->
                                    </div>
                                    
                                    <div id="noProducts" class="text-center py-4 text-muted">
                                        <i class="fas fa-box-open fa-2x mb-2"></i>
                                        <p class="mb-0">No products added. Click "Add Product" to start.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <!-- Summary -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="card-title mb-0 fw-bold">Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Items:</span>
                                        <span id="itemCount">0</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Total:</span>
                                        <span class="fw-bold fs-5" id="totalAmount">₹0.00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-theme rounded-pill" id="submitBtn" disabled>
                                            <i class="fas fa-check me-1"></i> Create Invoice
                                        </button>
                                        <a href="{{ route('vendor.invoices.index') }}" class="btn btn-outline-secondary rounded-pill">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Product</label>
                    <select class="form-select" id="productSelect">
                        <option value="">Select a product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" 
                                    data-name="{{ $product->name }}" 
                                    data-price="{{ $product->selling_price }}">
                                {{ $product->name }} - ₹{{ number_format($product->selling_price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="productPrice" step="0.01" min="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="productQuantity" value="1" min="1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-theme rounded-pill" id="confirmAddProduct">Add Product</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productsList = document.getElementById('productsList');
    const noProducts = document.getElementById('noProducts');
    const itemCount = document.getElementById('itemCount');
    const totalAmount = document.getElementById('totalAmount');
    const submitBtn = document.getElementById('submitBtn');
    const addProductModal = new bootstrap.Modal(document.getElementById('addProductModal'));
    
    let products = [];
    let productIndex = 0;

    // Open add product modal
    document.getElementById('addProductBtn').addEventListener('click', function() {
        document.getElementById('productSelect').value = '';
        document.getElementById('productPrice').value = '';
        document.getElementById('productQuantity').value = '1';
        addProductModal.show();
    });

    // When product is selected, fill price
    document.getElementById('productSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.value) {
            document.getElementById('productPrice').value = selected.dataset.price;
        }
    });

    // Add product to list
    document.getElementById('confirmAddProduct').addEventListener('click', function() {
        const select = document.getElementById('productSelect');
        const price = parseFloat(document.getElementById('productPrice').value) || 0;
        const quantity = parseInt(document.getElementById('productQuantity').value) || 1;
        
        if (!select.value || price <= 0) {
            alert('Please select a product and enter a valid price.');
            return;
        }

        const selected = select.options[select.selectedIndex];
        const product = {
            id: select.value,
            name: selected.dataset.name,
            price: price,
            quantity: quantity,
            total: price * quantity,
            index: productIndex++
        };

        products.push(product);
        renderProducts();
        addProductModal.hide();
    });

    function renderProducts() {
        if (products.length === 0) {
            productsList.innerHTML = '';
            noProducts.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            noProducts.style.display = 'none';
            submitBtn.disabled = false;
            
            productsList.innerHTML = products.map((p, i) => `
                <div class="product-item" data-index="${p.index}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${p.name}</h6>
                            <small class="text-muted">₹${p.price.toFixed(2)} × ${p.quantity}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold">₹${p.total.toFixed(2)}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle remove-product" data-index="${p.index}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="items[${i}][product_id]" value="${p.id}">
                    <input type="hidden" name="items[${i}][price]" value="${p.price}">
                    <input type="hidden" name="items[${i}][quantity]" value="${p.quantity}">
                </div>
            `).join('');

            // Add remove handlers
            document.querySelectorAll('.remove-product').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    products = products.filter(p => p.index !== index);
                    renderProducts();
                });
            });
        }

        // Update summary
        const total = products.reduce((sum, p) => sum + p.total, 0);
        itemCount.textContent = products.length;
        totalAmount.textContent = '₹' + total.toFixed(2);
    }

    renderProducts();
});
</script>
@endpush