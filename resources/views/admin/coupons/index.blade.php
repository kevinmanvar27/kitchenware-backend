@extends('admin.layouts.app')

@section('title', 'Coupons')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Coupon Management',
                'breadcrumbs' => [
                    'Coupons' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Coupons</h4>
                                        <p class="mb-0 text-muted small">Manage discount coupons for your customers</p>
                                    </div>
                                    <a href="{{ route('admin.coupons.create') }}" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4">
                                        <i class="fas fa-plus me-1 me-md-2"></i><span class="d-none d-sm-inline">Add New Coupon</span><span class="d-sm-none">Add</span>
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
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="couponsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>SR No.</th>
                                                <th>Code</th>
                                                <th>Discount</th>
                                                <th>Min Order</th>
                                                <th>Usage</th>
                                                <th>Valid Period</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($coupons as $key => $coupon)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>
                                                    <span class="badge bg-dark fs-6 font-monospace">{{ $coupon->code }}</span>
                                                    @if($coupon->description)
                                                        <br><small class="text-muted">{{ Str::limit($coupon->description, 30) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success">
                                                        @if($coupon->discount_type === 'percentage')
                                                            {{ $coupon->discount_value }}%
                                                        @else
                                                            ₹{{ number_format($coupon->discount_value, 2) }}
                                                        @endif
                                                    </span>
                                                    @if($coupon->discount_type === 'percentage' && $coupon->max_discount_amount)
                                                        <br><small class="text-muted">Max: ₹{{ number_format($coupon->max_discount_amount, 2) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($coupon->min_order_amount > 0)
                                                        ₹{{ number_format($coupon->min_order_amount, 2) }}
                                                    @else
                                                        <span class="text-muted">No minimum</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-medium">{{ $coupon->usage_count }}</span>
                                                    @if($coupon->usage_limit)
                                                        / {{ $coupon->usage_limit }}
                                                    @else
                                                        <span class="text-muted">/ ∞</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($coupon->valid_from || $coupon->valid_until)
                                                        @if($coupon->valid_from)
                                                            <small>From: {{ $coupon->valid_from->format('d M Y') }}</small><br>
                                                        @endif
                                                        @if($coupon->valid_until)
                                                            <small>Until: {{ $coupon->valid_until->format('d M Y') }}</small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">No expiry</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php $status = $coupon->status; @endphp
                                                    @switch($status)
                                                        @case('active')
                                                            <span class="badge bg-success rounded-pill">Active</span>
                                                            @break
                                                        @case('inactive')
                                                            <span class="badge bg-secondary rounded-pill">Inactive</span>
                                                            @break
                                                        @case('expired')
                                                            <span class="badge bg-danger rounded-pill">Expired</span>
                                                            @break
                                                        @case('scheduled')
                                                            <span class="badge bg-info rounded-pill">Scheduled</span>
                                                            @break
                                                        @case('exhausted')
                                                            <span class="badge bg-warning rounded-pill">Exhausted</span>
                                                            @break
                                                    @endswitch
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('admin.coupons.show', $coupon) }}" class="btn btn-outline-info rounded-start-pill px-3" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-outline-primary px-3" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-{{ $coupon->is_active ? 'warning' : 'success' }} px-3 toggle-status" 
                                                                data-id="{{ $coupon->id }}" title="{{ $coupon->is_active ? 'Deactivate' : 'Activate' }}">
                                                            <i class="fas fa-{{ $coupon->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                        <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-outline-danger rounded-end-pill px-3 delete-btn" title="Delete Coupon">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                                                        <p class="mb-2">No coupons found</p>
                                                        <p class="small mb-3">Create discount coupons to boost your sales</p>
                                                        <a href="{{ route('admin.coupons.create') }}" class="btn btn-theme btn-sm rounded-pill px-4">
                                                            <i class="fas fa-plus me-2"></i>Create Your First Coupon
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                <p class="mb-0">Are you sure you want to delete this coupon?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#couponsTable').DataTable({
        "order": [[5, "desc"]], // Sort by Valid Period (descending)
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "language": {
            "search": "Search coupons:",
            "lengthMenu": "Show _MENU_ coupons per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ coupons",
            "infoEmpty": "No coupons available",
            "infoFiltered": "(filtered from _MAX_ total coupons)",
            "zeroRecords": "No matching coupons found",
            "emptyTable": "No coupons available"
        },
        "columnDefs": [
            { "orderable": false, "targets": [7] } // Disable sorting on Actions column
        ],
        "responsive": true,
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Delete confirmation modal handling
    var deleteForm = null;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            deleteForm = this.closest('.delete-form');
            deleteModal.show();
        });
    });
    
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (deleteForm) {
            deleteForm.submit();
        }
    });
    
    // Toggle status buttons
    document.querySelectorAll('.toggle-status').forEach(button => {
        button.addEventListener('click', function() {
            const couponId = this.dataset.id;
            const btn = this;
            
            fetch(`/admin/coupons/${couponId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
</script>
@endsection
