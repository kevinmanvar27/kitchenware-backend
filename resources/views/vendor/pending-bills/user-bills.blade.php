@extends('vendor.layouts.app')

@section('title', ($customerData['name'] ?? $user->name) . ' - Pending Bills')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => ($customerData['name'] ?? $user->name) . ' - Bills'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Back Button -->
                <div class="mb-4">
                    <a href="{{ route('vendor.pending-bills.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-1"></i> Back to All Bills
                    </a>
                </div>

                <!-- User Info Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                @php
                                    $displayName = $customerData['name'] ?? $user->name;
                                    $displayAvatar = $customerData['avatar'] ?? $user->avatar;
                                @endphp
                                @if($displayAvatar)
                                    <img src="{{ asset('storage/' . $displayAvatar) }}" 
                                         class="rounded-circle" width="64" height="64">
                                @else
                                    <div class="bg-theme rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 64px; height: 64px;">
                                        <span class="text-white fs-4">{{ substr($displayName, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="col">
                                <h4 class="mb-1">
                                    {{ $displayName }}
                                    @if($user->name === 'Deleted User')
                                        <span class="badge bg-secondary ms-2">Account Deleted</span>
                                    @endif
                                </h4>
                                <p class="mb-0 text-muted">
                                    @php
                                        $displayMobile = $customerData['mobile_number'] ?? $user->mobile_number ?? null;
                                        $displayEmail = $customerData['email'] ?? null;
                                        // Don't show email if it's a deleted email format
                                        if ($displayEmail && (str_starts_with($displayEmail, 'deleted_') && str_ends_with($displayEmail, '@deleted.local'))) {
                                            $displayEmail = null;
                                        }
                                        // Use user email only if not deleted
                                        if (!$displayEmail && $user->email && !(str_starts_with($user->email, 'deleted_') && str_ends_with($user->email, '@deleted.local'))) {
                                            $displayEmail = $user->email;
                                        }
                                    @endphp
                                    <i class="fas fa-phone me-1"></i> {{ $displayMobile ?? 'N/A' }}
                                    @if($displayEmail)
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-envelope me-1"></i> {{ $displayEmail }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm bg-info text-white">
                            <div class="card-body text-center">
                                <h6 class="mb-1 opacity-75">Total Amount</h6>
                                <h3 class="mb-0 fw-bold">₹{{ number_format($totalAmount, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body text-center">
                                <h6 class="mb-1 opacity-75">Total Paid</h6>
                                <h3 class="mb-0 fw-bold">₹{{ number_format($totalPaid, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm bg-danger text-white">
                            <div class="card-body text-center">
                                <h6 class="mb-1 opacity-75">Total Pending</h6>
                                <h3 class="mb-0 fw-bold">₹{{ number_format($totalPending, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bills Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h4 class="card-title mb-0 fw-bold">{{ $customerData['name'] ?? $user->name }}'s Bills</h4>
                        <p class="mb-0 text-muted">All invoices for this customer</p>
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
                        
                        @if($invoices->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover" id="userBillsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Invoice #</th>
                                            <th>Date</th>
                                            <th>Order Status</th>
                                            <th>Total Amount</th>
                                            <th>Paid Amount</th>
                                            <th>Pending Amount</th>
                                            <th>Payment Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoices as $index => $invoice)
                                        @php
                                            $pendingAmount = $invoice->total_amount - $invoice->paid_amount;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <a href="{{ route('vendor.invoices.show', $invoice->id) }}" class="text-decoration-none">
                                                    {{ $invoice->invoice_number }}
                                                </a>
                                            </td>
                                            <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                            <td>
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
                                                @endswitch
                                            </td>
                                            <td class="fw-bold">₹{{ number_format($invoice->total_amount, 2) }}</td>
                                            <td class="text-success">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                                            <td class="text-danger fw-bold">₹{{ number_format($pendingAmount, 2) }}</td>
                                            <td>
                                                @switch($invoice->payment_status)
                                                    @case('unpaid')
                                                        <span class="badge bg-secondary">Unpaid</span>
                                                        @break
                                                    @case('partial')
                                                        <span class="badge bg-warning">Partial</span>
                                                        @break
                                                    @case('paid')
                                                        <span class="badge bg-success">Paid</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-success rounded-start-pill px-3" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#paymentModal{{ $invoice->id }}"
                                                            {{ $pendingAmount <= 0 ? 'disabled' : '' }}>
                                                        <i class="fas fa-plus"></i> Pay
                                                    </button>
                                                    <a href="{{ route('vendor.invoices.show', $invoice->id) }}" 
                                                       class="btn btn-outline-primary rounded-end-pill px-3">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Payment Modal -->
                                        <div class="modal fade" id="paymentModal{{ $invoice->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Add Payment - {{ $invoice->invoice_number }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('vendor.pending-bills.add-payment', $invoice->id) }}" method="POST">
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
                                                                    <input type="number" name="amount" id="vendorUserAmount{{ $invoice->id }}" class="form-control" 
                                                                           step="0.01" min="0.01" max="{{ $pendingAmount }}"
                                                                           placeholder="Enter amount" required>
                                                                </div>
                                                                <small class="text-muted">Max: ₹{{ number_format($pendingAmount, 2) }}</small>
                                                            </div>
                                                            <div class="d-grid gap-2 mb-3">
                                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                                        onclick="document.getElementById('vendorUserAmount{{ $invoice->id }}').value = '{{ number_format($pendingAmount, 2, '.', '') }}'">
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
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5 class="mb-2">No bills found</h5>
                                <p class="mb-0 text-muted">This customer has no invoices.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#userBillsTable').DataTable({
        "pageLength": 25,
        "ordering": true,
        "info": true,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": [8] }
        ],
        "order": [[2, 'desc']], // Sort by date descending
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries"
        }
    });
    $('.dataTables_length select').css('width', '80px');
});
</script>
@endsection
