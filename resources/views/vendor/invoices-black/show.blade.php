@extends('vendor.layouts.app')

@section('title', 'Without GST Invoice - ' . setting('site_title', 'Vendor Panel'))

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Without GST Invoice Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title mb-0 fw-bold">
                                            <span class="badge bg-secondary me-2">Without GST</span>
                                            Invoice Details
                                        </h4>
                                        <p class="mb-0 text-muted">Invoice #{{ $invoiceNumber }}</p>
                                    </div>
                                    <div>
                                        <a href="{{ route('vendor.invoices-black.index') }}" class="btn btn-outline-secondary rounded-pill">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Invoices
                                        </a>
                                        @if(!$invoice->isDraft())
                                        <a href="{{ route('vendor.invoices-black.download-pdf', $invoice->id) }}" class="btn btn-secondary rounded-pill ms-2">
                                            <i class="fas fa-file-pdf me-2"></i>Download PDF
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3 mb-4" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3 mb-4" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if($invoice->original_invoice_id)
                                    <div class="alert alert-info mb-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        This invoice was converted from a proforma invoice (Original ID: #{{ $invoice->original_invoice_id }})
                                    </div>
                                @endif
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5 class="fw-bold mb-3">From:</h5>
                                        @php
                                            $vendor = Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
                                        @endphp
                                        <p class="mb-1">{{ $vendor->store_name ?? $vendor->business_name ?? $vendor->name ?? setting('site_title', 'Store Name') }}</p>
                                        @if($vendor->business_address)
                                            <p class="mb-1">{{ $vendor->business_address }}@if($vendor->city), {{ $vendor->city }}@endif @if($vendor->state), {{ $vendor->state }}@endif @if($vendor->postal_code) - {{ $vendor->postal_code }}@endif</p>
                                        @elseif($vendor->address)
                                            <p class="mb-1">{{ $vendor->address }}</p>
                                        @else
                                            <p class="mb-1">{{ setting('address', 'Company Address') }}</p>
                                        @endif
                                        <p class="mb-1">{{ $vendor->business_email ?? $vendor->email ?? setting('company_email', 'company@example.com') }}</p>
                                        <p class="mb-1">{{ $vendor->business_phone ?? $vendor->phone ?? setting('company_phone', '+1 (555) 123-4567') }}</p>
                                        @if($vendor->gst_number)
                                            <p class="mb-1"><strong>GST:</strong> {{ $vendor->gst_number }}</p>
                                        @endif
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h5 class="fw-bold mb-3">To:</h5>
                                        @if($customer)
                                            <p class="mb-1">{{ $customer['name'] ?? 'N/A' }}</p>
                                            <p class="mb-1">{{ $customer['email'] ?? 'N/A' }}</p>
                                            @if(!empty($customer['address']))
                                                <p class="mb-1">{{ $customer['address'] }}</p>
                                            @endif
                                            @if(!empty($customer['mobile_number']))
                                                <p class="mb-1">{{ $customer['mobile_number'] }}</p>
                                            @endif
                                        @else
                                            <p class="mb-1">Guest Customer</p>
                                            <p class="mb-1">N/A</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Editable Invoice Form -->
                                <form id="invoiceForm" action="{{ route('vendor.invoices-black.update', $invoice->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                            
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Invoice #:</strong> {{ $invoiceNumber }}</p>
                                            <p class="mb-1"><strong>Date:</strong> {{ $invoiceDate }}</p>
                                            <div class="mb-2">
                                                <label for="gst_type" class="form-label"><strong>Invoice Type:</strong></label>
                                                <select name="gst_type" class="form-select form-select-sm" id="gst_type">
                                                    <option value="without_gst" selected>Without GST</option>
                                                    <option value="with_gst">With GST</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong>Status:</strong> 
                                                @switch($invoice->status)
                                                    @case('Draft')
                                                        <span class="badge bg-secondary">Draft</span>
                                                        @break
                                                    @case('Approved')
                                                        <span class="badge bg-success">Approved</span>
                                                        @break
                                                    @case('Dispatch')
                                                        <span class="badge bg-info">Dispatch</span>
                                                        @break
                                                    @case('Out for Delivery')
                                                        <span class="badge bg-primary">Out for Delivery</span>
                                                        @break
                                                    @case('Delivered')
                                                        <span class="badge bg-success">Delivered</span>
                                                        @break
                                                    @case('Return')
                                                        <span class="badge bg-danger">Return</span>
                                                        @break
                                                @endswitch
                                            </p>
                                            
                                            <!-- Status selection -->
                                            <div class="mt-2">
                                                <label for="status" class="form-label">Update Status:</label>
                                                <select name="status" class="form-select form-select-sm status-select" id="status">
                                                    @foreach(\App\Models\WithoutGstInvoice::STATUS_OPTIONS as $statusOption)
                                                        <option value="{{ $statusOption }}" {{ $invoice->status == $statusOption ? 'selected' : '' }}>{{ $statusOption }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Total</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($cartItems as $index => $item)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $item['product_name'] ?? $item['name'] ?? $item['title'] ?? 'Product' }}</strong>
                                                            @if(!empty($item['product_variation_id']))
                                                                {{-- Display attributes for variation products --}}
                                                                @if(!empty($item['variation_attributes']))
                                                                    <small class="text-muted d-block">
                                                                        @foreach($item['variation_attributes'] as $attrName => $attrValue)
                                                                            <strong>{{ $attrName }}:</strong> {{ $attrValue }}@if(!$loop->last), @endif
                                                                        @endforeach
                                                                    </small>
                                                                @endif
                                                                @if(!empty($item['variation_sku']))
                                                                    <small class="text-muted d-block"><strong>SKU:</strong> {{ $item['variation_sku'] }}</small>
                                                                @endif
                                                            @endif
                                                            @php
                                                                $description = $item['product_description'] ?? $item['description'] ?? null;
                                                            @endphp
                                                            @if(!empty($description))
                                                                <small class="text-muted d-block">{!! Str::limit(strip_tags($description), 50) !!}</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-text">₹</span>
                                                            <input type="number" name="items[{{ $index }}][price]" 
                                                                   class="form-control item-price" 
                                                                   value="{{ $item['price'] }}" 
                                                                   step="0.01" min="0">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][quantity]" 
                                                               class="form-control item-quantity" 
                                                               value="{{ $item['quantity'] }}" 
                                                               min="1">
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-text">₹</span>
                                                            <input type="number" name="items[{{ $index }}][total]" 
                                                                   class="form-control item-total" 
                                                                   value="{{ $item['total'] ?? ($item['price'] * $item['quantity']) }}" 
                                                                   step="0.01" min="0" readonly>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-item-btn" data-index="{{ $index }}">
                                                            <i class="fas fa-trash"></i> Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                        No products found in this invoice
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h5 class="card-title">Notes</h5>
                                                    <textarea name="notes" class="form-control" rows="3">{{ $invoiceData['notes'] ?? 'This is a without-GST invoice. No tax is applicable.' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="table-responsive">
                                                <table class="table table-borderless">
                                                    <tbody>
                                                        <tr>
                                                            <td class="fw-bold">Subtotal:</td>
                                                            <td class="text-end">
                                                                <div class="input-group">
                                                                    <span class="input-group-text">₹</span>
                                                                    <input type="number" name="subtotal" class="form-control subtotal" value="{{ number_format($invoiceData['subtotal'] ?? $total, 2, '.', '') }}" step="0.01" min="0" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-bold">Shipping:</td>
                                                            <td class="text-end">
                                                                <div class="input-group">
                                                                    <span class="input-group-text">₹</span>
                                                                    <input type="number" name="shipping" class="form-control shipping" value="{{ $invoiceData['shipping'] ?? 0 }}" step="0.01" min="0">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-bold">Discount Amount:</td>
                                                            <td class="text-end">
                                                                <div class="input-group">
                                                                    <span class="input-group-text">₹</span>
                                                                    <input type="number" name="discount_amount" class="form-control discount-amount" value="{{ $invoiceData['discount_amount'] ?? 0 }}" step="0.01" min="0">
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @if(!empty($invoiceData['coupon']) && !empty($invoiceData['coupon_discount']) && $invoiceData['coupon_discount'] > 0)
                                                        <tr>
                                                            <td class="fw-bold">
                                                                <span class="text-success">
                                                                    <i class="fas fa-ticket-alt me-1"></i>Coupon ({{ $invoiceData['coupon']['code'] }}):
                                                                </span>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="text-success fw-bold">-₹{{ number_format($invoiceData['coupon_discount'], 2) }}</span>
                                                                <input type="hidden" name="coupon_discount" value="{{ $invoiceData['coupon_discount'] }}">
                                                            </td>
                                                        </tr>
                                                        @endif
                                                        <tr class="border-top">
                                                            <td class="fw-bold">Total:</td>
                                                            <td class="text-end fw-bold">
                                                                <div class="input-group">
                                                                    <span class="input-group-text">₹</span>
                                                                    <input type="number" name="total" class="form-control total" value="{{ number_format($invoiceData['total'] ?? $total, 2, '.', '') }}" step="0.01" min="0" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <!-- GST Fields (shown only when "With GST" is selected) -->
                                            <div id="gstFieldsContainer" style="display: none;">
                                                <table class="table table-borderless mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td class="fw-bold">Tax Percentage:</td>
                                                            <td class="text-end">
                                                                <div class="input-group">
                                                                    <input type="number" name="tax_percentage" class="form-control tax-percentage" value="18" step="0.01" min="0" max="100">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-bold">Tax Amount:</td>
                                                            <td class="text-end">
                                                                <div class="input-group">
                                                                    <span class="input-group-text">₹</span>
                                                                    <input type="number" name="tax_amount" class="form-control tax-amount" value="0" step="0.01" min="0" readonly>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-secondary rounded-pill">
                                            <i class="fas fa-save me-1"></i>Save Invoice
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Hidden form for removing items -->
                                <form id="removeItemForm" action="{{ route('vendor.invoices-black.remove-item', $invoice->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="item_index" id="itemIndexInput">
                                </form>
                                
                                <!-- Payment Section -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body">
                                                <h5 class="card-title mb-3">Payment Information</h5>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <p class="mb-1"><strong>Total Amount:</strong> ₹{{ number_format($invoice->total_amount, 2) }}</p>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p class="mb-1"><strong>Paid Amount:</strong> ₹{{ number_format($invoice->paid_amount, 2) }}</p>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p class="mb-1">
                                                            <strong>Payment Status:</strong>
                                                            @switch($invoice->payment_status)
                                                                @case('paid')
                                                                    <span class="badge bg-success">Paid</span>
                                                                    @break
                                                                @case('partial')
                                                                    <span class="badge bg-warning">Partial</span>
                                                                    @break
                                                                @default
                                                                    <span class="badge bg-danger">Unpaid</span>
                                                            @endswitch
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                                @if($invoice->payment_status !== 'paid')
                                                <hr>
                                                <form action="{{ route('vendor.invoices-black.add-payment', $invoice->id) }}" method="POST" class="row g-3">
                                                    @csrf
                                                    <div class="col-md-6">
                                                        <label for="paymentAmount" class="form-label">Add Payment</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">₹</span>
                                                            <input type="number" class="form-control" id="paymentAmount" name="amount" 
                                                                   step="0.01" min="0.01" 
                                                                   max="{{ $invoice->total_amount - $invoice->paid_amount }}"
                                                                   placeholder="Enter amount">
                                                        </div>
                                                        <small class="text-muted">Pending: ₹{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</small>
                                                    </div>
                                                    <div class="col-md-6 d-flex align-items-end">
                                                        <button type="submit" class="btn btn-success rounded-pill">
                                                            <i class="fas fa-plus me-1"></i>Add Payment
                                                        </button>
                                                    </div>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // GST Type handling
    const gstTypeSelect = document.getElementById('gst_type');
    const gstFieldsContainer = document.getElementById('gstFieldsContainer');
    const taxPercentageInput = document.querySelector('.tax-percentage');
    const taxAmountInput = document.querySelector('.tax-amount');
    
    // Show/hide GST fields based on selection
    function toggleGstFields() {
        if (gstTypeSelect.value === 'with_gst') {
            gstFieldsContainer.style.display = 'block';
        } else {
            gstFieldsContainer.style.display = 'none';
            if (taxPercentageInput) taxPercentageInput.value = '0';
            if (taxAmountInput) taxAmountInput.value = '0';
        }
        calculateInvoiceTotals();
    }
    
    // Add event listener for GST type change
    if (gstTypeSelect) {
        gstTypeSelect.addEventListener('change', toggleGstFields);
    }
    
    // Calculate item totals and overall invoice totals
    function calculateItemTotal(row) {
        const priceInput = row.querySelector('.item-price');
        const quantityInput = row.querySelector('.item-quantity');
        const totalInput = row.querySelector('.item-total');
        
        if (priceInput && quantityInput && totalInput) {
            const price = parseFloat(priceInput.value) || 0;
            const quantity = parseInt(quantityInput.value) || 0;
            const total = price * quantity;
            totalInput.value = total.toFixed(2);
        }
    }
    
    // Calculate overall invoice totals
    function calculateInvoiceTotals() {
        // Calculate subtotal from item totals
        let subtotal = 0;
        document.querySelectorAll('.item-total').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });
        document.querySelector('.subtotal').value = subtotal.toFixed(2);
        
        // Get shipping
        const shipping = parseFloat(document.querySelector('.shipping').value) || 0;
        
        // Get discount amount
        const discountAmount = parseFloat(document.querySelector('.discount-amount').value) || 0;
        
        // Calculate tax based on GST type
        let taxAmount = 0;
        if (gstTypeSelect && gstTypeSelect.value === 'with_gst') {
            const taxPercentage = parseFloat(taxPercentageInput?.value) || 18;
            taxAmount = (subtotal * taxPercentage) / 100;
            if (taxAmountInput) taxAmountInput.value = taxAmount.toFixed(2);
        } else {
            if (taxAmountInput) taxAmountInput.value = '0';
        }
        
        // Calculate final total
        // Total = (Subtotal + Shipping + Tax) - Discount
        const finalTotal = (subtotal + shipping + taxAmount) - discountAmount;
        document.querySelector('.total').value = finalTotal.toFixed(2);
    }
    
    // Add event listeners to item inputs
    document.querySelectorAll('.item-price, .item-quantity').forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            calculateItemTotal(row);
            calculateInvoiceTotals();
        });
    });
    
    // Add event listeners to discount and shipping inputs
    const discountInput = document.querySelector('.discount-amount');
    const shippingInput = document.querySelector('.shipping');
    
    if (discountInput) {
        discountInput.addEventListener('input', calculateInvoiceTotals);
    }
    if (shippingInput) {
        shippingInput.addEventListener('input', calculateInvoiceTotals);
    }
    
    // Add event listener for tax percentage change
    if (taxPercentageInput) {
        taxPercentageInput.addEventListener('input', calculateInvoiceTotals);
    }
    
    // Handle item removal
    document.querySelectorAll('.remove-item-btn').forEach(button => {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            if (confirm('Are you sure you want to remove this item?')) {
                document.getElementById('itemIndexInput').value = index;
                document.getElementById('removeItemForm').submit();
            }
        });
    });
    
    // Initial calculation
    document.querySelectorAll('tbody tr').forEach(row => {
        calculateItemTotal(row);
    });
    toggleGstFields();
    calculateInvoiceTotals();
});
</script>
@endsection
