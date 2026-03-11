@extends('admin.layouts.app')

@section('title', 'Referral Earnings')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Referral Earnings',
                'breadcrumbs' => ['Referral Earnings' => null]
            ])

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 border-start border-warning border-4 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Pending Approval</h6>
                                    <h3 class="mb-2 fw-bold text-dark">₹{{ number_format($stats['total_pending'], 2) }}</h3>
                                    <small class="text-muted"><i class="fas fa-receipt me-1"></i>{{ $stats['count_pending'] }} earnings</small>
                                </div>
                                <div class="text-warning opacity-75">
                                    <i class="fas fa-clock fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 border-start border-success border-4 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Approved (Unpaid)</h6>
                                    <h3 class="mb-2 fw-bold text-dark">₹{{ number_format($stats['total_approved'], 2) }}</h3>
                                    <small class="text-muted"><i class="fas fa-receipt me-1"></i>{{ $stats['count_approved'] }} earnings</small>
                                </div>
                                <div class="text-success opacity-75">
                                    <i class="fas fa-check-circle fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 border-start border-primary border-4 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Paid</h6>
                                    <h3 class="mb-2 fw-bold text-dark">₹{{ number_format($stats['total_paid'], 2) }}</h3>
                                    <small class="text-muted"><i class="fas fa-receipt me-1"></i>{{ $stats['count_paid'] }} earnings</small>
                                </div>
                                <div class="text-primary opacity-75">
                                    <i class="fas fa-money-bill-wave fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 border-start border-info border-4 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total All Time</h6>
                                    <h3 class="mb-2 fw-bold text-dark">₹{{ number_format($stats['total_pending'] + $stats['total_approved'] + $stats['total_paid'], 2) }}</h3>
                                    <small class="text-muted"><i class="fas fa-receipt me-1"></i>{{ $stats['count_pending'] + $stats['count_approved'] + $stats['count_paid'] }} earnings</small>
                                </div>
                                <div class="text-info opacity-75">
                                    <i class="fas fa-chart-line fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-filter me-2 text-theme"></i>Filter Earnings
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('admin.referral-earnings.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-medium small text-muted mb-2">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium small text-muted mb-2">Vendor</label>
                            <select name="referrer_vendor_id" class="form-select">
                                <option value="">All Vendors</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('referrer_vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->store_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium small text-muted mb-2">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="ID, referral code, vendor name, subscription ID..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-theme rounded-pill px-4 flex-grow-1">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('admin.referral-earnings.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Reset Filters">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Earnings Table -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-gift me-2 text-theme"></i>Referral Earnings
                    </h5>
                    @if(request('status') == 'pending' && $earnings->count() > 0)
                    <button type="button" class="btn btn-success rounded-pill px-4" onclick="bulkApprove()">
                        <i class="fas fa-check-double me-2"></i>Bulk Approve Selected
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="earningsTable">
                            <thead class="table-light">
                                <tr>
                                    @if(request('status') == 'pending')
                                    <th width="50" class="text-center ps-4">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    @endif
                                    <th class="ps-4">ID</th>
                                    <th>Referrer (Earns)</th>
                                    <th>Referred (Purchased)</th>
                                    <th>Referral Code</th>
                                    <th class="text-end">Subscription Amount</th>
                                    <th class="text-end">Commission</th>
                                    <th class="text-center">Status</th>
                                    <th>Date</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($earnings as $earning)
                                <tr>
                                    @if(request('status') == 'pending')
                                    <td class="text-center ps-4">
                                        <input type="checkbox" class="form-check-input earning-checkbox" value="{{ $earning->id }}">
                                    </td>
                                    @endif
                                    <td class="fw-medium ps-4">#{{ $earning->id }}</td>
                                    <td>
                                        <div class="d-flex flex-column py-2">
                                            <span class="fw-medium text-dark">{{ $earning->referrerVendor->store_name }}</span>
                                            <small class="text-muted">{{ $earning->referrerVendor->business_email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column py-2">
                                            <span class="fw-medium text-dark">{{ $earning->referredVendor->store_name }}</span>
                                            <small class="text-muted">{{ $earning->referredVendor->business_email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-theme rounded-pill px-3 py-2">{{ $earning->referral_code }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-medium text-dark">₹{{ number_format($earning->subscription_amount, 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex flex-column align-items-end py-2">
                                            <span class="fw-bold text-success">₹{{ number_format($earning->commission_amount, 2) }}</span>
                                            <small class="text-muted">({{ $earning->commission_percentage }}%)</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($earning->status == 'pending')
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Pending</span>
                                        @elseif($earning->status == 'approved')
                                            <span class="badge bg-success rounded-pill px-3 py-2">Approved</span>
                                        @elseif($earning->status == 'paid')
                                            <span class="badge bg-primary rounded-pill px-3 py-2">Paid</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3 py-2">{{ ucfirst($earning->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column py-2">
                                            <span class="fw-medium text-dark">{{ $earning->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $earning->created_at->diffForHumans() }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.referral-earnings.show', $earning->id) }}" class="btn btn-outline-info rounded-start-pill px-3" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($earning->canBeApproved())
                                            <form action="{{ route('admin.referral-earnings.approve', $earning->id) }}" method="POST" class="d-inline approve-form">
                                                @csrf
                                                <button type="button" class="btn btn-outline-success rounded-end-pill px-3 approve-btn" title="Approve Earning">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ request('status') == 'pending' ? '10' : '9' }}" class="text-center py-5">
                                        <div class="text-muted py-5">
                                            <i class="fas fa-inbox fa-4x mb-3 opacity-50"></i>
                                            <p class="mb-2 fs-5 fw-medium">No referral earnings found</p>
                                            <p class="small">Earnings will appear here when vendors use referral codes</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($earnings->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $earnings->links() }}
                </div>
                @endif
            </div>
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveConfirmModal" tabindex="-1" aria-labelledby="approveConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="approveConfirmModalLabel">Confirm Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4 px-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success fa-4x"></i>
                </div>
                <h6 class="mb-2 fw-medium">Are you sure you want to approve this earning?</h6>
                <p class="text-muted small mb-0">This will mark the earning as approved and ready for payout.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pt-0 pb-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success rounded-pill px-4" id="confirmApproveBtn">
                    <i class="fas fa-check me-1"></i>Approve
                </button>
            </div>
        </div>
    </div>
</div>

@if(request('status') == 'pending')
<form id="bulkApproveForm" action="{{ route('admin.referral-earnings.bulk-approve') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="earning_ids" id="bulkEarningIds">
</form>
@endif

@endsection

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#earningsTable').DataTable({
        "order": [[1, "desc"]], // Sort by ID (descending - newest first)
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "language": {
            "search": "Search earnings:",
            "lengthMenu": "Show _MENU_ earnings per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ earnings",
            "infoEmpty": "No earnings available",
            "infoFiltered": "(filtered from _MAX_ total earnings)",
            "zeroRecords": "No matching earnings found",
            "emptyTable": "No referral earnings found"
        },
        "columnDefs": [
            { "orderable": false, "targets": {{ request('status') == 'pending' ? '[0, 9]' : '[8]' }} }, // Disable sorting on checkbox and Actions columns
            { 
                "targets": {{ request('status') == 'pending' ? '[5, 6]' : '[4, 5]' }}, // Amount columns
                "type": "num"
            }
        ],
        "responsive": true,
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Select all checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.earning-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Approve confirmation modal handling
    var approveForm = null;
    var approveModal = new bootstrap.Modal(document.getElementById('approveConfirmModal'));
    
    document.querySelectorAll('.approve-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            approveForm = this.closest('.approve-form');
            approveModal.show();
        });
    });
    
    document.getElementById('confirmApproveBtn').addEventListener('click', function() {
        if (approveForm) {
            approveForm.submit();
        }
    });
});

// Bulk approve function
function bulkApprove() {
    const checkedBoxes = document.querySelectorAll('.earning-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select at least one earning to approve.');
        return;
    }
    
    if (!confirm(`Approve ${checkedBoxes.length} selected earnings?`)) {
        return;
    }
    
    const ids = Array.from(checkedBoxes).map(cb => cb.value);
    document.getElementById('bulkEarningIds').value = JSON.stringify(ids);
    document.getElementById('bulkApproveForm').submit();
}
</script>
@endpush
