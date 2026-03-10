@extends('admin.layouts.app')

@section('title', 'Referral Users - ' . $referral->name)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Referred Users',
                'breadcrumbs' => [
                    'Referrals' => route('admin.referrals.index'),
                    $referral->name => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Back Button -->
                <div class="mb-3">
                    <a href="{{ route('admin.referrals.index') }}" class="btn btn-outline-secondary rounded-pill btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Referrals
                    </a>
                </div>

                <!-- Referral Info Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h5 class="mb-1 fw-bold">{{ $referral->name }}</h5>
                                <div class="d-flex align-items-center gap-2 flex-wrap text-muted">
                                    @if($referral->phone_number)
                                        <span><i class="fas fa-phone me-1"></i>{{ $referral->phone_number }}</span>
                                    @endif
                                    <span class="badge bg-dark font-monospace">{{ $referral->referral_code }}</span>
                                    <span class="badge {{ $referral->getStatusBadgeClass() }}">{{ ucfirst($referral->status) }}</span>
                                </div>
                            </div>
                            <div class="col-md-8 mt-3 mt-md-0">
                                <div class="row text-center">
                                    <div class="col-4 col-md-2">
                                        <h4 class="mb-0 fw-bold text-theme">₹{{ number_format($referral->amount, 2) }}</h4>
                                        <small class="text-muted">Per User</small>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <h4 class="mb-0 fw-bold text-info">{{ $paymentStats['total_users'] }}</h4>
                                        <small class="text-muted">Total Users</small>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <h4 class="mb-0 fw-bold text-success">{{ $paymentStats['paid_users'] }}</h4>
                                        <small class="text-muted">Paid</small>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <h4 class="mb-0 fw-bold text-warning">{{ $paymentStats['pending_users'] }}</h4>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <h4 class="mb-0 fw-bold text-success">₹{{ number_format($paymentStats['total_paid'], 2) }}</h4>
                                        <small class="text-muted">Total Paid</small>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <h4 class="mb-0 fw-bold text-danger">₹{{ number_format($paymentStats['total_pending'], 2) }}</h4>
                                        <small class="text-muted">Pending Amt</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Add User Form -->
                    <div class="col-12 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-user-plus me-2 text-theme"></i>Add Referred User
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                                        <ul class="mb-0 small">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <form action="{{ route('admin.referrals.users.store', $referral) }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="user_name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                            id="user_name" name="name" value="{{ old('name') }}" 
                                            placeholder="Enter user name" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="user_email" class="form-label">Email</label>
                                        <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" 
                                            id="user_email" name="email" value="{{ old('email') }}" 
                                            placeholder="Enter email address">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="user_phone" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control form-control-sm @error('phone_number') is-invalid @enderror" 
                                            id="user_phone" name="phone_number" value="{{ old('phone_number') }}" 
                                            placeholder="Enter phone number">
                                        @error('phone_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="user_notes" class="form-label">Notes</label>
                                        <textarea class="form-control form-control-sm @error('notes') is-invalid @enderror" 
                                            id="user_notes" name="notes" rows="2" 
                                            placeholder="Any additional notes">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <button type="submit" class="btn btn-theme btn-sm rounded-pill w-100">
                                        <i class="fas fa-plus me-2"></i>Add User
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Users List -->
                    <div class="col-12 col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <h6 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-users me-2 text-theme"></i>Referred Users ({{ $paymentStats['total_users'] }})
                                    </h6>
                                    @if($paymentStats['pending_users'] > 0)
                                        <form action="{{ route('admin.referrals.users.mark-multiple-paid', $referral) }}" method="POST" id="bulkPaymentForm">
                                            @csrf
                                            <input type="hidden" name="user_ids[]" id="bulkUserIds" value="">
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3" id="markAllPaidBtn" disabled>
                                                <i class="fas fa-check-double me-1"></i>Mark Selected as Paid
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-2" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-2" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                @if($referralUsers->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle" id="referralUsersTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 40px;">
                                                        <input type="checkbox" class="form-check-input" id="selectAllUsers">
                                                    </th>
                                                    <th>#</th>
                                                    <th>User Details</th>
                                                    <th>Contact</th>
                                                    <th class="text-center">Payment Status</th>
                                                    <th class="text-end">Amount</th>
                                                    <th>Paid On</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($referralUsers as $index => $referralUser)
                                                <tr class="{{ $referralUser->isPaid() ? 'table-success' : '' }}">
                                                    <td>
                                                        @if($referralUser->isPending())
                                                            <input type="checkbox" class="form-check-input user-checkbox" 
                                                                   value="{{ $referralUser->id }}" data-id="{{ $referralUser->id }}">
                                                        @else
                                                            <i class="fas fa-lock text-muted" title="Payment completed - locked"></i>
                                                        @endif
                                                    </td>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <div class="fw-medium">{{ $referralUser->name }}</div>
                                                        @if($referralUser->user)
                                                            <small class="text-success"><i class="fas fa-check-circle me-1"></i>Linked User</small>
                                                        @endif
                                                        @if($referralUser->notes)
                                                            <div class="text-muted small" title="{{ $referralUser->notes }}">
                                                                <i class="fas fa-sticky-note me-1"></i>{{ Str::limit($referralUser->notes, 25) }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($referralUser->email)
                                                            <div><a href="mailto:{{ $referralUser->email }}" class="text-decoration-none small">{{ $referralUser->email }}</a></div>
                                                        @endif
                                                        @if($referralUser->phone_number)
                                                            <div><a href="tel:{{ $referralUser->phone_number }}" class="text-decoration-none small">{{ $referralUser->phone_number }}</a></div>
                                                        @endif
                                                        @if(!$referralUser->email && !$referralUser->phone_number)
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($referralUser->isPaid())
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check me-1"></i>Paid
                                                            </span>
                                                        @else
                                                            <span class="badge bg-warning text-dark">
                                                                <i class="fas fa-clock me-1"></i>Pending
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        @if($referralUser->isPaid())
                                                            <span class="text-success fw-medium">₹{{ number_format($referralUser->payment_amount, 2) }}</span>
                                                        @else
                                                            <span class="text-muted">₹{{ number_format($referral->amount, 2) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($referralUser->isPaid() && $referralUser->paid_at)
                                                            <small class="text-muted">{{ $referralUser->paid_at->format('d M Y') }}</small>
                                                            @if($referralUser->payment_notes)
                                                                <div class="text-muted small" title="{{ $referralUser->payment_notes }}">
                                                                    <i class="fas fa-comment me-1"></i>{{ Str::limit($referralUser->payment_notes, 15) }}
                                                                </div>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            @if($referralUser->isPending())
                                                                <button type="button" class="btn btn-success rounded-start-pill px-3 mark-paid-btn" 
                                                                        data-id="{{ $referralUser->id }}"
                                                                        data-name="{{ $referralUser->name }}"
                                                                        data-amount="{{ $referral->amount }}"
                                                                        title="Mark as Paid">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                                <form action="{{ route('admin.referrals.users.destroy', [$referral, $referralUser]) }}" method="POST" class="d-inline delete-form">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="button" class="btn btn-outline-danger rounded-end-pill px-3 delete-btn" title="Remove User">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <button type="button" class="btn btn-secondary rounded-pill px-3" disabled title="Payment completed - cannot modify">
                                                                    <i class="fas fa-lock me-1"></i>Locked
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <p class="mb-0">No users have been referred yet</p>
                                            <p class="small">Add users manually using the form on the left</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1" aria-labelledby="markPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="markPaidModalLabel">Confirm Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-4">
                    <i class="fas fa-money-bill-wave text-success fa-3x mb-3"></i>
                    <p class="mb-1">Mark payment as completed for:</p>
                    <h5 class="fw-bold" id="paymentUserName"></h5>
                    <h4 class="text-success fw-bold">₹<span id="paymentAmount"></span></h4>
                </div>
                <div class="mb-3">
                    <label for="paymentNotes" class="form-label">Payment Notes (Optional)</label>
                    <input type="text" class="form-control" id="paymentNotes" placeholder="e.g., Transaction ID, Payment method">
                </div>
                <div class="alert alert-warning py-2 small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Note:</strong> Once marked as paid, this cannot be changed.
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success rounded-pill px-4" id="confirmPaymentBtn">
                    <i class="fas fa-check me-1"></i>Confirm Payment
                </button>
            </div>
        </div>
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
                <p class="mb-0">Are you sure you want to remove this user?</p>
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
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Variables for modals
    var deleteForm = null;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    var markPaidModal = new bootstrap.Modal(document.getElementById('markPaidModal'));
    var currentUserId = null;
    
    // Delete confirmation modal handling
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
    
    // Mark as Paid modal handling
    document.querySelectorAll('.mark-paid-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentUserId = this.dataset.id;
            document.getElementById('paymentUserName').textContent = this.dataset.name;
            document.getElementById('paymentAmount').textContent = parseFloat(this.dataset.amount).toFixed(2);
            document.getElementById('paymentNotes').value = '';
            markPaidModal.show();
        });
    });
    
    document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
        if (!currentUserId) return;
        
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
        
        const paymentNotes = document.getElementById('paymentNotes').value;
        
        fetch(`/admin/referrals/{{ $referral->id }}/users/${currentUserId}/mark-paid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ payment_notes: paymentNotes })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                markPaidModal.hide();
                // Reload page to show updated status
                window.location.reload();
            } else {
                alert(data.message || 'Failed to mark payment as completed');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Confirm Payment';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Confirm Payment';
        });
    });
    
    // Select all checkbox handling
    const selectAllCheckbox = document.getElementById('selectAllUsers');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const markAllPaidBtn = document.getElementById('markAllPaidBtn');
    const bulkPaymentForm = document.getElementById('bulkPaymentForm');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateBulkButton();
        });
    }
    
    userCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updateBulkButton);
    });
    
    function updateBulkButton() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        if (markAllPaidBtn) {
            markAllPaidBtn.disabled = checkedBoxes.length === 0;
            markAllPaidBtn.innerHTML = checkedBoxes.length > 0 
                ? `<i class="fas fa-check-double me-1"></i>Mark ${checkedBoxes.length} as Paid`
                : '<i class="fas fa-check-double me-1"></i>Mark Selected as Paid';
        }
    }
    
    // Bulk payment form submission
    if (markAllPaidBtn && bulkPaymentForm) {
        markAllPaidBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            if (checkedBoxes.length === 0) return;
            
            if (!confirm(`Are you sure you want to mark ${checkedBoxes.length} user(s) as paid? This action cannot be undone.`)) {
                return;
            }
            
            // Clear existing hidden inputs
            const existingInputs = bulkPaymentForm.querySelectorAll('input[name="user_ids[]"]');
            existingInputs.forEach(input => input.remove());
            
            // Add new hidden inputs for each selected user
            checkedBoxes.forEach(function(checkbox) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = checkbox.value;
                bulkPaymentForm.appendChild(input);
            });
            
            bulkPaymentForm.submit();
        });
    }
});
</script>

<script>
// Initialize DataTables for Referral Users Table
$(document).ready(function() {
    if ($('#referralUsersTable').length && $('#referralUsersTable tbody tr').length > 0) {
        $('#referralUsersTable').DataTable({
            "order": [[7, "desc"]], // Sort by date (paid on column) descending
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "columnDefs": [
                { "orderable": false, "targets": [0, 7] }, // Disable sorting on checkbox and actions columns
                { "type": "date", "targets": [6] } // Treat paid on column as date
            ],
            "responsive": true,
            "language": {
                "search": "Search users:",
                "lengthMenu": "Show _MENU_ users per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ users",
                "infoEmpty": "No users available",
                "infoFiltered": "(filtered from _MAX_ total users)",
                "zeroRecords": "No matching users found",
                "emptyTable": "No referred users yet"
            },
            "drawCallback": function() {
                // Reinitialize tooltips after table redraw
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
                
                // Reattach event listeners for dynamically loaded buttons
                attachEventListeners();
            }
        });
    }
});

// Function to attach event listeners (called on page load and after DataTables redraw)
function attachEventListeners() {
    // Delete confirmation modal handling
    document.querySelectorAll('.delete-btn').forEach(function(button) {
        // Remove existing listeners to prevent duplicates
        button.replaceWith(button.cloneNode(true));
    });
    
    document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            deleteForm = this.closest('.delete-form');
            deleteModal.show();
        });
    });
    
    // Mark as Paid modal handling
    document.querySelectorAll('.mark-paid-btn').forEach(function(button) {
        // Remove existing listeners to prevent duplicates
        button.replaceWith(button.cloneNode(true));
    });
    
    document.querySelectorAll('.mark-paid-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentUserId = this.dataset.id;
            document.getElementById('paymentUserName').textContent = this.dataset.name;
            document.getElementById('paymentAmount').textContent = parseFloat(this.dataset.amount).toFixed(2);
            document.getElementById('paymentNotes').value = '';
            markPaidModal.show();
        });
    });
    
    // Update checkbox listeners
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    userCheckboxes.forEach(function(checkbox) {
        checkbox.replaceWith(checkbox.cloneNode(true));
    });
    
    document.querySelectorAll('.user-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', updateBulkButton);
    });
}
</script>
@endsection
