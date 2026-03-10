@extends('vendor.layouts.app')

@section('title', 'Invoice - ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Invoice Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <!-- Payment Status Card -->
                        @php
                            $pendingAmount = $invoice->total_amount - $invoice->paid_amount;
                        @endphp
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Payment Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded">
                                            <small class="text-muted d-block">Total Amount</small>
                                            <h5 class="mb-0 fw-bold">₹{{ number_format($invoice->total_amount, 2) }}</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-success rounded">
                                            <small class="text-white d-block">Paid Amount</small>
                                            <h5 class="mb-0 fw-bold text-white">₹{{ number_format($invoice->paid_amount, 2) }}</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-danger rounded">
                                            <small class="text-white d-block">Pending Amount</small>
                                            <h5 class="mb-0 fw-bold text-white">₹{{ number_format($pendingAmount, 2) }}</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 rounded">
                                            <small class="text-muted d-block">Payment Status</small>
                                            @switch($invoice->payment_status)
                                                @case('unpaid')
                                                    <span class="badge bg-secondary fs-6">Unpaid</span>
                                                    @break
                                                @case('partial')
                                                    <span class="badge bg-warning fs-6">Partial</span>
                                                    @break
                                                @case('paid')
                                                    <span class="badge bg-success fs-6">Paid</span>
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                </div>
                                @if($pendingAmount > 0)
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-success rounded-pill px-4" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#paymentModal">
                                        <i class="fas fa-plus me-1"></i> Add Payment
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title mb-0 fw-bold">Invoice Details</h4>
                                        <p class="mb-0 text-muted">Invoice #{{ $invoice->invoice_number }}</p>
                                    </div>
                                    <div>
                                        <a href="{{ route('vendor.invoices.index') }}" class="btn btn-outline-secondary rounded-pill">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Invoices
                                        </a>
                                        @if($invoice->status !== 'Draft')
                                        <a href="{{ route('vendor.invoices.download', $invoice) }}" class="btn btn-theme rounded-pill ms-2">
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
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5 class="fw-bold mb-3">From:</h5>
                                        @php
                                            $vendor = Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
                                        @endphp
                                        <p class="mb-1">{{ $vendor->store_name ?? $vendor->business_name ?? $vendor->name ?? 'Store Name' }}</p>
                                        @if($vendor->business_address)
                                            <p class="mb-1">{{ $vendor->business_address }}@if($vendor->city), {{ $vendor->city }}@endif @if($vendor->state), {{ $vendor->state }}@endif @if($vendor->postal_code) - {{ $vendor->postal_code }}@endif</p>
                                        @elseif($vendor->address)
                                            <p class="mb-1">{{ $vendor->address }}</p>
                                        @else
                                            <p class="mb-1">Store Address</p>
                                        @endif
                                        <p class="mb-1">{{ $vendor->business_email ?? $vendor->email ?? Auth::user()->email }}</p>
                                        <p class="mb-1">{{ $vendor->business_phone ?? $vendor->phone ?? Auth::user()->phone ?? '' }}</p>
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
                                            @elseif(!empty($customer['phone']))
                                                <p class="mb-1">{{ $customer['phone'] }}</p>
                                            @endif
                                        @elseif($invoice->user)
                                            <p class="mb-1">{{ $invoice->user->name }}</p>
                                            <p class="mb-1">{{ $invoice->user->email }}</p>
                                            @if($invoice->user->address)
                                                <p class="mb-1">{{ $invoice->user->address }}</p>
                                            @endif
                                            @if($invoice->user->phone)
                                                <p class="mb-1">{{ $invoice->user->phone }}</p>
                                            @endif
                                        @else
                                            <p class="mb-1">Guest Customer</p>
                                            <p class="mb-1">N/A</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Editable Invoice Form -->
                                <form id="invoiceForm" action="{{ route('vendor.invoices.update', $invoice->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                            
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                                            <p class="mb-1"><strong>Date:</strong> {{ $invoiceDate }}</p>
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
                                            
                                            <!-- Status selection moved inside main form -->
                                            <div class="mt-2">
                                                <label for="status" class="form-label">Update Status:</label>
                                                <select name="status" class="form-select form-select-sm status-select" id="status">
                                                    @foreach(['Draft', 'Approved', 'Dispatch', 'Out for Delivery', 'Delivered', 'Return'] as $statusOption)
                                                        <option value="{{ $statusOption }}" {{ $invoice->status == $statusOption ? 'selected' : '' }}>{{ $statusOption }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <!-- GST Type Selection -->
                                            <div class="mt-2">
                                                <label for="gst_type" class="form-label">Invoice Type:</label>
                                                <select name="gst_type" class="form-select form-select-sm" id="gst_type">
                                                    <option value="with_gst" {{ ($invoiceData['gst_type'] ?? 'with_gst') == 'with_gst' ? 'selected' : '' }}>With GST</option>
                                                    <option value="without_gst" {{ ($invoiceData['gst_type'] ?? 'with_gst') == 'without_gst' ? 'selected' : '' }}>Without GST</option>
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
                                                        <!-- Changed from form to button with JavaScript handling -->
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
                                                    <textarea name="notes" class="form-control" rows="3">{{ $invoiceData['notes'] ?? 'This is a proforma invoice and not a tax invoice. Payment is due upon receipt.' }}</textarea>
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
                                                        <tr class="gst-row" style="{{ ($invoiceData['gst_type'] ?? 'with_gst') == 'without_gst' ? 'display: none;' : '' }}">
                                                            <td class="fw-bold">GST (%):</td>
                                                            <td class="text-end">
                                                                <div class="input-group">
                                                                    <input type="number" name="tax_percentage" class="form-control tax-percentage" value="{{ ($invoiceData['gst_type'] ?? 'with_gst') == 'without_gst' ? 0 : ($invoiceData['tax_percentage'] ?? 18) }}" step="0.01" min="0" max="100">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr class="gst-row" style="{{ ($invoiceData['gst_type'] ?? 'with_gst') == 'without_gst' ? 'display: none;' : '' }}">
                                                            <td class="fw-bold">Tax Amount:</td>
                                                            <td class="text-end">
                                                                <div class="input-group">
                                                                    <span class="input-group-text">₹</span>
                                                                    <input type="number" name="tax_amount" class="form-control tax-amount" value="{{ ($invoiceData['gst_type'] ?? 'with_gst') == 'without_gst' ? 0 : ($invoiceData['tax_amount'] ?? 0) }}" step="0.01" min="0" readonly>
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
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-theme rounded-pill">
                                            <i class="fas fa-save me-1"></i>Save Invoice
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Hidden form for removing items -->
                                <form id="removeItemForm" action="{{ route('vendor.invoices.remove-item', $invoice->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="item_index" id="itemIndexInput">
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

<!-- Payment Modal -->
@if($pendingAmount > 0)
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment - {{ $invoice->invoice_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('vendor.pending-bills.add-payment', $invoice->id) }}" method="POST" id="paymentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <small class="text-muted">Total</small>
                                <h6 class="mb-0">₹{{ number_format($invoice->total_amount, 2) }}</h6>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Paid</small>
                                <h6 class="mb-0 text-success">₹{{ number_format($invoice->paid_amount, 2) }}</h6>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Pending</small>
                                <h6 class="mb-0 text-danger">₹{{ number_format($pendingAmount, 2) }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="amount" id="paymentAmount" class="form-control" 
                                   step="0.01" min="0.01" max="{{ $pendingAmount }}"
                                   placeholder="Enter amount" required>
                        </div>
                        <small class="text-muted">Max: ₹{{ number_format($pendingAmount, 2) }}</small>
                    </div>
                    <div class="d-grid gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="payFullAmountBtn"
                                data-amount="{{ number_format($pendingAmount, 2, '.', '') }}">
                            <i class="fas fa-money-bill-wave me-1"></i> Pay Full Amount (₹{{ number_format($pendingAmount, 2) }})
                        </button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">Select Payment Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Note (Optional)</label>
                        <textarea name="payment_note" class="form-control" rows="2" placeholder="Add any notes about this payment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill">
                        <i class="fas fa-check me-1"></i> Add Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pay Full Amount button handler
    const payFullAmountBtn = document.getElementById('payFullAmountBtn');
    const paymentAmountInput = document.getElementById('paymentAmount');
    
    if (payFullAmountBtn && paymentAmountInput) {
        payFullAmountBtn.addEventListener('click', function() {
            const amount = this.getAttribute('data-amount');
            paymentAmountInput.value = amount;
        });
    }

    // GST Type Elements
    const gstTypeSelect = document.getElementById('gst_type');
    const gstRows = document.querySelectorAll('.gst-row');
    const taxPercentageInput = document.querySelector('.tax-percentage');
    const taxAmountInput = document.querySelector('.tax-amount');
    
    // Store original tax percentage for restoration
    let originalTaxPercentage = parseFloat(taxPercentageInput?.value) || 18;
    
    // GST Type toggle functionality
    if (gstTypeSelect) {
        gstTypeSelect.addEventListener('change', function() {
            const isWithoutGst = this.value === 'without_gst';
            
            // Show/Hide GST rows
            gstRows.forEach(row => {
                row.style.display = isWithoutGst ? 'none' : '';
            });
            
            if (isWithoutGst) {
                // Store current value before resetting (only if it's not already 0)
                if (parseFloat(taxPercentageInput?.value) > 0) {
                    originalTaxPercentage = parseFloat(taxPercentageInput.value);
                }
                // Set tax to 0 when Without GST
                if (taxPercentageInput) taxPercentageInput.value = '0';
                if (taxAmountInput) taxAmountInput.value = '0';
            } else {
                // Restore original tax percentage when With GST
                if (taxPercentageInput) taxPercentageInput.value = originalTaxPercentage.toFixed(2);
            }
            
            // Recalculate totals
            calculateInvoiceTotals();
        });
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
        const subtotalInput = document.querySelector('.subtotal');
        if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
        
        // Check GST type
        const isWithoutGst = gstTypeSelect?.value === 'without_gst';
        
        // Get tax percentage (force 0 if without GST)
        let taxPercentage = 0;
        if (!isWithoutGst && taxPercentageInput) {
            taxPercentage = parseFloat(taxPercentageInput.value) || 0;
        }
        
        // Get shipping
        const shippingInput = document.querySelector('.shipping');
        const shipping = parseFloat(shippingInput?.value) || 0;
        
        // Get discount amount
        const discountInput = document.querySelector('.discount-amount');
        const discountAmount = parseFloat(discountInput?.value) || 0;
        
        // Calculate tax amount (tax on subtotal only, not including shipping)
        const taxAmount = isWithoutGst ? 0 : (subtotal * taxPercentage / 100);
        if (taxAmountInput) taxAmountInput.value = taxAmount.toFixed(2);
        
        // Calculate final total
        // With GST: Total = (Subtotal + Shipping + Tax Amount) - Discount
        // Without GST: Total = (Subtotal + Shipping) - Discount
        const finalTotal = (subtotal + shipping + taxAmount) - discountAmount;
        const totalInput = document.querySelector('.total');
        if (totalInput) totalInput.value = finalTotal.toFixed(2);
    }
    
    // Add event listeners to item inputs
    document.querySelectorAll('.item-price, .item-quantity').forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            calculateItemTotal(row);
            calculateInvoiceTotals();
        });
    });
    
    // Add event listeners to discount, shipping, and tax inputs
    const discountAmountInput = document.querySelector('.discount-amount');
    const shippingInput = document.querySelector('.shipping');
    
    if (discountAmountInput) {
        discountAmountInput.addEventListener('input', calculateInvoiceTotals);
    }
    if (shippingInput) {
        shippingInput.addEventListener('input', calculateInvoiceTotals);
    }
    if (taxPercentageInput) {
        taxPercentageInput.addEventListener('input', function() {
            // Update original tax percentage when manually changed (only if With GST)
            if (gstTypeSelect?.value !== 'without_gst') {
                originalTaxPercentage = parseFloat(this.value) || 0;
            }
            calculateInvoiceTotals();
        });
    }
    
    // Handle remove item buttons
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            if (confirm('Are you sure you want to remove this item?')) {
                document.getElementById('itemIndexInput').value = index;
                document.getElementById('removeItemForm').submit();
            }
        });
    });
    
    // Initial calculation
    calculateInvoiceTotals();
});
</script>
@endsection