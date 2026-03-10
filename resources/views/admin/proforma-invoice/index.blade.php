@extends('admin.layouts.app')

@section('title', 'Proforma Invoices - ' . setting('site_title', 'Admin Panel'))

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Proforma Invoices'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body text-center">
                                <h6 class="mb-1 opacity-75">Total Invoices</h6>
                                <h3 class="mb-0 fw-bold">{{ $totalInvoices }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-info text-white">
                            <div class="card-body text-center">
                                <h6 class="mb-1 opacity-75">Total Amount</h6>
                                <h3 class="mb-0 fw-bold">₹{{ number_format($totalAmount, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body text-center">
                                <h6 class="mb-1 opacity-75">Delivered</h6>
                                <h3 class="mb-0 fw-bold">{{ $statusCounts['Delivered'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm bg-warning text-white">
                            <div class="card-body text-center">
                                <h6 class="mb-1 opacity-75">Pending</h6>
                                <h3 class="mb-0 fw-bold">{{ $statusCounts['Approved'] + $statusCounts['Dispatch'] + $statusCounts['Out for Delivery'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Invoices</h4>
                                        <p class="mb-0 text-muted small">Manage user invoices</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Filters -->
                                <div class="card mb-4 border">
                                    <div class="card-body">
                                        <form method="GET" action="{{ route('admin.proforma-invoice.index') }}" id="filterForm">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Client</label>
                                                    <select name="client" class="form-select">
                                                        <option value="">All Clients</option>
                                                        @foreach($clients as $client)
                                                            <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>
                                                                {{ $client->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fw-semibold">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="">All Status</option>
                                                        <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                                        <option value="Dispatch" {{ request('status') == 'Dispatch' ? 'selected' : '' }}>Dispatch</option>
                                                        <option value="Out for Delivery" {{ request('status') == 'Out for Delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                                        <option value="Delivered" {{ request('status') == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                                        <option value="Return" {{ request('status') == 'Return' ? 'selected' : '' }}>Return</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fw-semibold">Date From</label>
                                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fw-semibold">Date To</label>
                                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                                </div>
                                                <div class="col-md-3 d-flex align-items-end gap-2">
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                                        <i class="fas fa-filter me-1"></i> Filter
                                                    </button>
                                                    <a href="{{ route('admin.proforma-invoice.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                                        <i class="fas fa-redo me-1"></i> Reset
                                                    </a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if($proformaInvoices->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="proformaInvoicesTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Invoice #</th>
                                                    <th>Customer</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($proformaInvoices as $index => $invoice)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $invoice->invoice_number }}</td>
                                                    <td>
                                                        @php
                                                            $invoiceData = $invoice->invoice_data;
                                                            if (is_string($invoiceData)) {
                                                                $invoiceData = json_decode($invoiceData, true);
                                                            }
                                                            $customerName = $invoiceData['customer']['name'] ?? null;
                                                        @endphp
                                                        @if($customerName)
                                                            {{ $customerName }}
                                                        @elseif($invoice->user)
                                                            {{ $invoice->user->name }}
                                                        @else
                                                            Guest ({{ substr($invoice->session_id, 0, 8) }}...)
                                                        @endif
                                                    </td>
                                                    <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                                                    <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
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
                                                            @case('Return')
                                                                <span class="badge bg-danger">Return</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ $invoice->status ?? 'Pending' }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="{{ route('admin.proforma-invoice.show', $invoice->id) }}" class="btn btn-outline-primary rounded-start-pill px-3" title="View/Edit Invoice" data-bs-toggle="tooltip">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <form action="{{ route('admin.proforma-invoice.destroy', $invoice->id) }}" method="POST" class="d-inline delete-form">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" class="btn btn-outline-danger rounded-end-pill px-3 delete-btn" title="Delete Invoice" data-bs-toggle="tooltip">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                        <h5 class="mb-2">No proforma invoices found</h5>
                                        <p class="mb-0 text-muted">Proforma invoices will appear here once generated by users.</p>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete this invoice? This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Delete confirmation modal
    let formToSubmit = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            formToSubmit = this.closest('form');
            deleteModal.show();
        });
    });
    
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (formToSubmit) {
            formToSubmit.submit();
        }
    });
    
    // Handle status form submission
    document.querySelectorAll('.status-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const select = form.querySelector('.status-select');
            const selectedStatus = select.value;
            const currentStatus = select.options[select.selectedIndex].text;
            
            this.submit();
        });
        
        // Auto-submit when status changes
        const select = form.querySelector('.status-select');
        select.addEventListener('change', function() {
            form.dispatchEvent(new Event('submit'));
        });
    });
    
    $('#proformaInvoicesTable').DataTable({
        "pageLength": 25,
        "ordering": true,
        "info": true,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": [6] } // Disable ordering on Actions column
        ],
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
    // Adjust select width after DataTable initializes
    $('.dataTables_length select').css('width', '80px');
});
</script>
@endsection