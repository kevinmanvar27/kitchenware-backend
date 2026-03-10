@extends('admin.layouts.app')

@section('title', 'Referral Codes')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Referral Codes',
                'breadcrumbs' => [
                    'Referral Codes' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-6 col-md-2 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-3">
                                <div class="text-primary mb-2">
                                    <i class="fas fa-ticket-alt fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                                <small class="text-muted">Total Codes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-3">
                                <div class="text-info mb-2">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">{{ $stats['total_referred_users'] }}</h3>
                                <small class="text-muted">Referred Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-3">
                                <div class="text-success mb-2">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">{{ $stats['paid_payments'] }}</h3>
                                <small class="text-muted">Paid Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-3">
                                <div class="text-warning mb-2">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">{{ $stats['pending_payments'] }}</h3>
                                <small class="text-muted">Pending Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-3">
                                <div class="text-success mb-2">
                                    <i class="fas fa-rupee-sign fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">₹{{ number_format($stats['total_paid_amount'], 2) }}</h3>
                                <small class="text-muted">Total Paid</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-3">
                                <div class="text-danger mb-2">
                                    <i class="fas fa-hourglass-half fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">₹{{ number_format($stats['total_pending_amount'], 2) }}</h3>
                                <small class="text-muted">Pending Amount</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referrals List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                            <div class="mb-2 mb-md-0">
                                <h5 class="card-title mb-0 fw-bold">Referral Codes</h5>
                                <p class="mb-0 text-muted small">Manage all referral codes and track payments</p>
                            </div>
                            <a href="{{ route('admin.referrals.create') }}" class="btn btn-sm btn-theme rounded-pill px-3">
                                <i class="fas fa-plus me-1"></i>Create Referral Code
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
                            <table class="table table-hover align-middle" id="referralsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Referrer</th>
                                        <th>Referral Code</th>
                                        <th class="text-center">Amount/User</th>
                                        <th class="text-center">Referred Users</th>
                                        <th class="text-center">Paid</th>
                                        <th class="text-center">Pending</th>
                                        <th class="text-end">Total Paid</th>
                                        <th class="text-end">Pending Amt</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($referrals as $referral)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $referral->name }}</div>
                                            @if($referral->phone_number)
                                                <small class="text-muted">{{ $referral->phone_number }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-dark fs-6 font-monospace">{{ $referral->referral_code }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('{{ $referral->referral_code }}')" title="Copy code">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-medium text-theme">₹{{ number_format($referral->amount, 2) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.referrals.users', $referral) }}" class="btn btn-sm btn-outline-info rounded-pill px-3" title="View Referred Users">
                                                <i class="fas fa-users me-1"></i>{{ $referral->referral_users_count }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $referral->paid_users_count ?? 0 }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if(($referral->pending_users_count ?? 0) > 0)
                                                <span class="badge bg-warning text-dark">{{ $referral->pending_users_count }}</span>
                                            @else
                                                <span class="badge bg-secondary">0</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success fw-medium">₹{{ number_format($referral->total_paid_amount ?? 0, 2) }}</span>
                                        </td>
                                        <td class="text-end">
                                            @if(($referral->calculated_pending_amount ?? 0) > 0)
                                                <span class="text-danger fw-medium">₹{{ number_format($referral->calculated_pending_amount, 2) }}</span>
                                            @else
                                                <span class="text-muted">₹0.00</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" type="checkbox" 
                                                    data-id="{{ $referral->id }}" 
                                                    {{ $referral->status == 'active' ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.referrals.users', $referral) }}" class="btn btn-outline-info rounded-start-pill px-3" title="View Users & Payments">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.referrals.edit', $referral) }}" class="btn btn-outline-primary px-3" title="Edit Referral">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.referrals.destroy', $referral) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-outline-danger rounded-end-pill px-3 delete-btn" title="Delete Referral">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                                                <p class="mb-2">No referral codes found</p>
                                                <p class="small mb-3">Create referral codes to grow your customer base</p>
                                                <a href="{{ route('admin.referrals.create') }}" class="btn btn-theme btn-sm rounded-pill px-4">
                                                    <i class="fas fa-plus me-2"></i>Create Your First Referral Code
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
                <p class="mb-0">Are you sure you want to delete this referral code?</p>
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
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show a brief toast or alert
        alert('Referral code copied to clipboard!');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#referralsTable').DataTable({
        "order": [[0, "asc"]], // Sort by Referrer name (ascending)
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "language": {
            "search": "Search referrals:",
            "lengthMenu": "Show _MENU_ referrals per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ referrals",
            "infoEmpty": "No referrals available",
            "infoFiltered": "(filtered from _MAX_ total referrals)",
            "zeroRecords": "No matching referrals found",
            "emptyTable": "No referral codes found"
        },
        "columnDefs": [
            { "orderable": false, "targets": [8, 9] }, // Disable sorting on Status toggle and Actions columns
            { 
                "targets": [2, 6, 7], // Amount/User, Total Paid, Pending Amt columns
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
    
    // Delete confirmation modal handling
    var deleteForm = null;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    document.querySelectorAll('.delete-btn').forEach(function(button) {
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
    
    // Status toggle handler
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const referralId = this.dataset.id;
            const newStatus = this.checked ? 'active' : 'inactive';
            
            fetch(`/admin/referrals/${referralId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert toggle if failed
                    this.checked = !this.checked;
                    alert(data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                // Revert toggle on error
                this.checked = !this.checked;
                console.error('Error:', error);
            });
        });
    });
});
</script>
@endsection
