@extends('admin.layouts.app')

@section('title', 'Pending Bills - ' . setting('site_title', 'Admin Panel'))

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Pending Bills'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white">Total Bills</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $totalBills }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-file-invoice fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white">Total Amount</h6>
                                        <h3 class="mb-0 fw-bold text-white">₹{{ number_format($totalAmount, 2) }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-rupee-sign fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white">Total Paid</h6>
                                        <h3 class="mb-0 fw-bold text-white">₹{{ number_format($totalPaid, 2) }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-check-circle fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white">Total Pending</h6>
                                        <h3 class="mb-0 fw-bold text-white">₹{{ number_format($totalPending, 2) }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-clock fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Summary -->
                <div class="row mb-4">
                    <div class="col-4 col-md-4 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-2 py-md-3">
                                <span class="badge bg-secondary fs-6 fs-md-5 px-3 px-md-4 py-2">{{ $unpaidBills }}</span>
                                <p class="mb-0 mt-2 text-muted small">Unpaid Bills</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 col-md-4 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-2 py-md-3">
                                <span class="badge bg-warning fs-6 fs-md-5 px-3 px-md-4 py-2">{{ $partialBills }}</span>
                                <p class="mb-0 mt-2 text-muted small">Partial Paid</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 col-md-4 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-2 py-md-3">
                                <span class="badge bg-success fs-6 fs-md-5 px-3 px-md-4 py-2">{{ $paidBills }}</span>
                                <p class="mb-0 mt-2 text-muted small">Fully Paid</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form action="{{ route('admin.pending-bills.index') }}" method="GET" class="row g-3">
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label">Customer</label>
                                <select name="user_id" class="form-select">
                                    <option value="">All Customers</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" class="form-select">
                                    <option value="">Pending Only</option>
                                    <option value="unpaid" {{ $paymentStatus == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    <option value="partial" {{ $paymentStatus == 'partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="paid" {{ $paymentStatus == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="all" {{ $paymentStatus == 'all' ? 'selected' : '' }}>All Status</option>
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <label class="form-label">From Date</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <label class="form-label">To Date</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 d-flex align-items-end gap-2 flex-wrap">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1 flex-md-grow-0">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('admin.pending-bills.index') }}" class="btn btn-outline-secondary rounded-pill px-4 flex-grow-1 flex-md-grow-0">
                                    <i class="fas fa-times me-1"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bills Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                        <div>
                            <h4 class="card-title mb-0 fw-bold">
                                @if($paymentStatus == 'all')
                                    All Bills
                                @elseif($paymentStatus == 'paid')
                                    Paid Bills
                                @elseif($paymentStatus == 'unpaid')
                                    Unpaid Bills
                                @elseif($paymentStatus == 'partial')
                                    Partially Paid Bills
                                @else
                                    Pending Bills
                                @endif
                            </h4>
                            <p class="mb-0 text-muted small">
                                @if($paymentStatus == 'all')
                                    Showing all bills
                                @else
                                    Showing bills with pending payments
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('admin.pending-bills.user-summary') }}" class="btn btn-outline-primary rounded-pill btn-sm">
                            <i class="fas fa-users me-1"></i> User Summary
                        </a>
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
                                <table class="table table-hover" id="pendingBillsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Invoice #</th>
                                            <th class="d-none d-md-table-cell">Customer</th>
                                            <th class="d-none d-lg-table-cell">Date</th>
                                            <th>Total</th>
                                            <th class="d-none d-sm-table-cell">Paid</th>
                                            <th>Pending</th>
                                            <th>Status</th>
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
                                                <a href="{{ route('admin.proforma-invoice.show', $invoice->id) }}" class="text-decoration-none">
                                                    {{ $invoice->invoice_number }}
                                                </a>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                @if($invoice->user && $invoice->user_id)
                                                    @php
                                                        $displayName = $invoice->user->name;
                                                        // If user is deleted, try to get original name from invoice_data
                                                        if ($displayName === 'Deleted User') {
                                                            $invoiceData = is_array($invoice->invoice_data) ? $invoice->invoice_data : json_decode($invoice->invoice_data, true);
                                                            if (isset($invoiceData['customer']['name'])) {
                                                                $displayName = $invoiceData['customer']['name'];
                                                            }
                                                        }
                                                    @endphp
                                                    <a href="{{ route('admin.pending-bills.user', $invoice->user_id) }}" class="text-decoration-none">
                                                        {{ $displayName }}
                                                        @if($invoice->user->name === 'Deleted User')
                                                            <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">Deleted</span>
                                                        @endif
                                                    </a>
                                                @else
                                                    <span class="text-muted">Guest</span>
                                                @endif
                                            </td>
                                            <td class="d-none d-lg-table-cell">{{ $invoice->created_at->format('d M Y') }}</td>
                                            <td class="fw-bold">₹{{ number_format($invoice->total_amount, 2) }}</td>
                                            <td class="text-success d-none d-sm-table-cell">₹{{ number_format($invoice->paid_amount, 2) }}</td>
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
                                                    <button type="button" class="btn btn-outline-success rounded-start-pill px-2 px-md-3" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#paymentModal{{ $invoice->id }}"
                                                            {{ $pendingAmount <= 0 ? 'disabled' : '' }}
                                                            title="Add Payment">
                                                        <i class="fas fa-plus"></i><span class="d-none d-md-inline ms-1">Pay</span>
                                                    </button>
                                                    <a href="{{ route('admin.proforma-invoice.show', $invoice->id) }}" 
                                                       class="btn btn-outline-primary rounded-end-pill px-2 px-md-3"
                                                       title="View Invoice Details">
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
                                                    <form action="{{ route('admin.pending-bills.add-payment', $invoice->id) }}" method="POST">
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
                                                                    <input type="number" name="amount" id="amount{{ $invoice->id }}" class="form-control" 
                                                                           step="0.01" min="0.01" max="{{ $pendingAmount }}"
                                                                           placeholder="Enter amount" required>
                                                                </div>
                                                                <small class="text-muted">Max: ₹{{ number_format($pendingAmount, 2) }}</small>
                                                            </div>
                                                            <div class="d-grid gap-2 mb-3">
                                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                                        onclick="document.getElementById('amount{{ $invoice->id }}').value = '{{ number_format($pendingAmount, 2, '.', '') }}'">
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
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <h5 class="mb-2">No bills found</h5>
                                <p class="mb-0 text-muted">Bills will appear here once invoices are generated.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    $('#pendingBillsTable').DataTable({
        "pageLength": 25,
        "ordering": true,
        "info": true,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": [8] }
        ],
        "order": [[6, 'desc']], // Sort by pending amount descending
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
    $('.dataTables_length select').css('width', '80px');
});
</script>
@endsection
