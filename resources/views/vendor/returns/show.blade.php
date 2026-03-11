@extends('vendor.layouts.app')

@section('title', 'Return Details - #' . $return->id)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Return Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <!-- Return Status Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title mb-0 fw-bold">Return Request #{{ $return->id }}</h4>
                                        <p class="mb-0 text-muted">Created on {{ $return->created_at->format('d M Y, h:i A') }}</p>
                                    </div>
                                    <div>
                                        <a href="{{ route('vendor.returns.index') }}" class="btn btn-outline-secondary rounded-pill">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Returns
                                        </a>
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
                                
                                <!-- Status Badge -->
                                <div class="text-center mb-4">
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
                                    <h3>
                                        <span class="badge bg-{{ $statusColor }}">
                                            {{ ucfirst(str_replace('_', ' ', $return->status)) }}
                                        </span>
                                    </h3>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="text-center mb-4">
                                    @if($return->status === 'pending')
                                        <button type="button" class="btn btn-success rounded-pill px-4 me-2" onclick="approveReturn({{ $return->id }})">
                                            <i class="fas fa-check me-2"></i>Approve Return
                                        </button>
                                        <button type="button" class="btn btn-danger rounded-pill px-4" onclick="rejectReturn({{ $return->id }})">
                                            <i class="fas fa-times me-2"></i>Reject Return
                                        </button>
                                    @endif
                                    
                                    @if(in_array($return->status, ['approved', 'pickup_scheduled', 'picked_up', 'received', 'inspected']))
                                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="updateStatus({{ $return->id }})">
                                            <i class="fas fa-sync me-2"></i>Update Status
                                        </button>
                                    @endif
                                    
                                    @if($return->status === 'inspected' && !$return->refund_processed_at)
                                        <button type="button" class="btn btn-warning rounded-pill px-4" onclick="processRefund({{ $return->id }})">
                                            <i class="fas fa-money-bill-wave me-2"></i>Process Refund
                                        </button>
                                    @endif
                                </div>
                                
                                <hr>
                                
                                <!-- Return Information -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5 class="fw-bold mb-3">Customer Information</h5>
                                        @if($return->user)
                                            <p class="mb-1"><strong>Name:</strong> {{ $return->user->name }}</p>
                                            <p class="mb-1"><strong>Email:</strong> {{ $return->user->email }}</p>
                                            <p class="mb-1"><strong>Phone:</strong> {{ $return->user->phone ?? 'N/A' }}</p>
                                            <p class="mb-1"><strong>Type:</strong> <span class="badge bg-primary">Web Customer</span></p>
                                        @elseif($return->vendorCustomer)
                                            <p class="mb-1"><strong>Name:</strong> {{ $return->vendorCustomer->name }}</p>
                                            <p class="mb-1"><strong>Email:</strong> {{ $return->vendorCustomer->email }}</p>
                                            <p class="mb-1"><strong>Phone:</strong> {{ $return->vendorCustomer->mobile_number ?? 'N/A' }}</p>
                                            <p class="mb-1"><strong>Type:</strong> <span class="badge bg-info">App Customer</span></p>
                                            <p class="mb-1"><strong>Wallet Balance:</strong> ₹{{ number_format($return->vendorCustomer->wallet_balance ?? 0, 2) }}</p>
                                        @else
                                            <p class="text-muted">Customer information not available</p>
                                        @endif
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h5 class="fw-bold mb-3">Invoice Information</h5>
                                        @if($return->invoice)
                                            <p class="mb-1"><strong>Invoice #:</strong> 
                                                <a href="{{ route('vendor.invoices.show', $return->invoice_id) }}" class="text-decoration-none">
                                                    {{ $return->invoice->invoice_number }}
                                                </a>
                                            </p>
                                            <p class="mb-1"><strong>Invoice Date:</strong> {{ $return->invoice->created_at->format('d M Y') }}</p>
                                            <p class="mb-1"><strong>Invoice Amount:</strong> ₹{{ number_format($return->invoice->total_amount, 2) }}</p>
                                            <p class="mb-1"><strong>Invoice Status:</strong> 
                                                <span class="badge bg-secondary">{{ $return->invoice->status }}</span>
                                            </p>
                                        @else
                                            <p class="text-muted">Invoice information not available</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- Return Details -->
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <h5 class="fw-bold mb-3">Return Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>Return Reason:</strong></p>
                                                <p class="text-muted">{{ $return->return_reason }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>Refund Amount:</strong></p>
                                                <h4 class="text-success">₹{{ number_format($return->refund_amount, 2) }}</h4>
                                            </div>
                                        </div>
                                        
                                        @if($return->rejection_reason)
                                        <div class="alert alert-danger mt-3">
                                            <strong>Rejection Reason:</strong> {{ $return->rejection_reason }}
                                        </div>
                                        @endif
                                        
                                        @if($return->notes)
                                        <div class="alert alert-info mt-3">
                                            <strong>Notes:</strong> {{ $return->notes }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Return Items -->
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <h5 class="fw-bold mb-3">Return Items</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Variation</th>
                                                        <th>Quantity</th>
                                                        <th>Price</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($return->return_items as $item)
                                                    <tr>
                                                        <td>{{ $item['product_name'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['variation_name'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['quantity'] ?? 0 }}</td>
                                                        <td>₹{{ number_format($item['price'] ?? 0, 2) }}</td>
                                                        <td>₹{{ number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2) }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Images -->
                                @if($return->images && count($return->images) > 0)
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <h5 class="fw-bold mb-3">Product Images</h5>
                                        <div class="row">
                                            @foreach($return->images as $image)
                                            <div class="col-md-3 mb-3">
                                                <a href="{{ Storage::url($image) }}" target="_blank">
                                                    <img src="{{ Storage::url($image) }}" class="img-fluid rounded shadow-sm" alt="Return Image">
                                                </a>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <hr>
                                
                                <!-- Timeline -->
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <h5 class="fw-bold mb-3">Return Timeline</h5>
                                        <div class="timeline">
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-success"></div>
                                                <div class="timeline-content">
                                                    <h6 class="mb-1">Return Requested</h6>
                                                    <p class="text-muted mb-0">{{ $return->created_at->format('d M Y, h:i A') }}</p>
                                                </div>
                                            </div>
                                            
                                            @if($return->reviewed_at)
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-{{ $return->status === 'rejected' ? 'danger' : 'info' }}"></div>
                                                <div class="timeline-content">
                                                    <h6 class="mb-1">{{ $return->status === 'rejected' ? 'Rejected' : 'Reviewed' }}</h6>
                                                    <p class="text-muted mb-0">{{ $return->reviewed_at->format('d M Y, h:i A') }}</p>
                                                    @if($return->reviewedBy)
                                                        <p class="text-muted mb-0">By: {{ $return->reviewedBy->name }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                            
                                            @if($return->refund_processed_at)
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-warning"></div>
                                                <div class="timeline-content">
                                                    <h6 class="mb-1">Refund Processed</h6>
                                                    <p class="text-muted mb-0">{{ $return->refund_processed_at->format('d M Y, h:i A') }}</p>
                                                    <p class="text-muted mb-0">Amount: ₹{{ number_format($return->refund_amount, 2) }}</p>
                                                </div>
                                            </div>
                                            @endif
                                            
                                            @if($return->status === 'completed')
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-success"></div>
                                                <div class="timeline-content">
                                                    <h6 class="mb-1">Completed</h6>
                                                    <p class="text-muted mb-0">{{ $return->updated_at->format('d M Y, h:i A') }}</p>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                    <div class="mb-3">
                        <label for="refund_method" class="form-label">Refund Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="refund_method" name="refund_method" required>
                            <option value="">Select Method</option>
                            <option value="wallet" selected>Wallet</option>
                            <option value="original_method">Original Payment Method</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="refund_amount" class="form-label">Refund Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="refund_amount" name="refund_amount" 
                               value="{{ $return->return_amount }}" step="0.01" min="0" max="{{ $return->return_amount }}" required>
                        <small class="text-muted">Maximum: ₹{{ number_format($return->return_amount, 2) }}</small>
                    </div>
                    <div class="mb-3">
                        <label for="refund_reference" class="form-label">Transaction Reference (Optional)</label>
                        <input type="text" class="form-control" id="refund_reference" name="refund_reference" 
                               placeholder="e.g., TXN123456789">
                    </div>
                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">Admin Notes (Optional)</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" 
                                  placeholder="Add any internal notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Process Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 20px;
    width: 2px;
    height: calc(100% - 10px);
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    padding-left: 10px;
}
</style>
@endpush

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
