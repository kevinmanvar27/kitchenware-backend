@extends('vendor.layouts.app')

@section('title', 'Product Returns')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Product Returns'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white">Pending</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $pendingCount }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-clock fa-lg"></i>
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
                                        <h6 class="mb-1 text-white">Under Review</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $underReviewCount }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-search fa-lg"></i>
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
                                        <h6 class="mb-1 text-white">Completed</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $completedCount }}</h3>
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
                                        <h6 class="mb-1 text-white">Rejected</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $rejectedCount }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-times-circle fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form action="{{ route('vendor.returns.index') }}" method="GET" class="row g-3">
                            <div class="col-6 col-sm-6 col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select rounded-pill">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="pickup_scheduled" {{ request('status') == 'pickup_scheduled' ? 'selected' : '' }}>Pickup Scheduled</option>
                                    <option value="picked_up" {{ request('status') == 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                                    <option value="inspected" {{ request('status') == 'inspected' ? 'selected' : '' }}>Inspected</option>
                                    <option value="refund_processing" {{ request('status') == 'refund_processing' ? 'selected' : '' }}>Refund Processing</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <label class="form-label">Customer Type</label>
                                <select name="customer_type" class="form-select rounded-pill">
                                    <option value="">All Types</option>
                                    <option value="user" {{ request('customer_type') == 'user' ? 'selected' : '' }}>Frontend</option>
                                    <option value="vendor_customer" {{ request('customer_type') == 'vendor_customer' ? 'selected' : '' }}>App</option>
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <label class="form-label">From Date</label>
                                <input type="date" name="date_from" class="form-control rounded-pill" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <label class="form-label">To Date</label>
                                <input type="date" name="date_to" class="form-control rounded-pill" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 d-flex align-items-end gap-2 flex-wrap">
                                <button type="submit" class="btn btn-theme rounded-pill px-4 flex-grow-1 flex-md-grow-0">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('vendor.returns.index') }}" class="btn btn-outline-secondary rounded-pill px-4 flex-grow-1 flex-md-grow-0">
                                    <i class="fas fa-times me-1"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Returns Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0 fw-bold">All Returns</h4>
                            <p class="mb-0 text-muted small">Manage product return requests</p>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('vendor.returns.export.excel') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-success rounded-pill me-2">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </a>
                            <a href="{{ route('vendor.returns.export.pdf') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-danger rounded-pill">
                                <i class="fas fa-file-pdf me-1"></i> Export PDF
                            </a>
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
                        
                        @if($returns->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Return ID</th>
                                            <th class="d-none d-md-table-cell">Invoice #</th>
                                            <th class="d-none d-lg-table-cell">Customer</th>
                                            <th class="d-none d-lg-table-cell">Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($returns as $index => $return)
                                        <tr>
                                            <td>{{ $returns->firstItem() + $index }}</td>
                                            <td>
                                                <a href="{{ route('vendor.returns.show', $return) }}" class="text-decoration-none fw-medium">
                                                    #{{ $return->id }}
                                                </a>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <a href="{{ route('vendor.invoices.show', $return->invoice_id) }}" class="text-decoration-none">
                                                    {{ $return->invoice->invoice_number ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td class="d-none d-lg-table-cell">
                                                @if($return->user)
                                                    {{ $return->user->name }}
                                                    <span class="badge bg-primary">Web</span>
                                                @elseif($return->vendorCustomer)
                                                    {{ $return->vendorCustomer->name }}
                                                    <span class="badge bg-info">App</span>
                                                @else
                                                    <span class="text-muted">Unknown</span>
                                                @endif
                                            </td>
                                            <td class="d-none d-lg-table-cell">{{ $return->created_at->format('d M Y') }}</td>
                                            <td class="fw-bold">₹{{ number_format($return->refund_amount, 2) }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'under_review' => 'info',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'pickup_scheduled' => 'primary',
                                                        'picked_up' => 'primary',
                                                        'received' => 'info',
                                                        'inspected' => 'info',
                                                        'refund_processing' => 'warning',
                                                        'completed' => 'success',
                                                        'cancelled' => 'secondary',
                                                    ];
                                                    $statusColor = $statusColors[$return->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $return->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('vendor.returns.show', $return) }}" class="btn btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($return->status === 'pending')
                                                        <button type="button" class="btn btn-outline-success" onclick="approveReturn({{ $return->id }})" title="Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" onclick="rejectReturn({{ $return->id }})" title="Reject">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if(in_array($return->status, ['approved', 'pickup_scheduled', 'picked_up', 'received', 'inspected']))
                                                        <button type="button" class="btn btn-outline-info" onclick="updateStatus({{ $return->id }})" title="Update Status">
                                                            <i class="fas fa-sync"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($return->status === 'inspected' && !$return->refund_processed_at)
                                                        <button type="button" class="btn btn-outline-warning" onclick="processRefund({{ $return->id }})" title="Process Refund">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted">
                                    Showing {{ $returns->firstItem() }} to {{ $returns->lastItem() }} of {{ $returns->total() }} returns
                                </div>
                                <div>
                                    {{ $returns->links() }}
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-undo fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No returns found</h5>
                                <p class="text-muted">There are no product returns matching your criteria.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Approve Return Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Approve Return Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to approve this return request?</p>
                    <div class="mb-3">
                        <label for="approve_notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="approve_notes" name="notes" rows="3" placeholder="Add any notes for the customer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Return Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Return Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Return Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateStatusForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_status" class="form-label">New Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="new_status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="pickup_scheduled">Pickup Scheduled</option>
                            <option value="picked_up">Picked Up</option>
                            <option value="received">Received</option>
                            <option value="inspected">Inspected</option>
                            <option value="refund_processing">Refund Processing</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status_notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="status_notes" name="notes" rows="3" placeholder="Add any notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Process Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalLabel">Process Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="refundForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Process refund to customer's wallet?</p>
                    <div class="mb-3">
                        <label for="refund_notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="refund_notes" name="notes" rows="3" placeholder="Add any notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Process Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function approveReturn(returnId) {
    const form = document.getElementById('approveForm');
    form.action = `/vendor/returns/${returnId}/approve`;
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectReturn(returnId) {
    const form = document.getElementById('rejectForm');
    form.action = `/vendor/returns/${returnId}/reject`;
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function updateStatus(returnId) {
    const form = document.getElementById('updateStatusForm');
    form.action = `/vendor/returns/${returnId}/update-status`;
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}

function processRefund(returnId) {
    const form = document.getElementById('refundForm');
    form.action = `/vendor/returns/${returnId}/process-refund`;
    const modal = new bootstrap.Modal(document.getElementById('refundModal'));
    modal.show();
}
</script>
@endpush
