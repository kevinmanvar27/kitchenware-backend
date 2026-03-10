@extends('admin.layouts.app')

@section('title', 'Leads')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Lead Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Leads</h4>
                                        <p class="mb-0 text-muted small">Manage all leads</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if(auth()->user()->hasPermission('view_lead'))
                                            <a href="{{ route('admin.leads.trashed') }}" class="btn btn-sm btn-md-normal btn-outline-secondary rounded-pill px-3 px-md-4">
                                                <i class="fas fa-trash-alt me-1 me-md-2"></i><span class="d-none d-sm-inline">Trashed</span>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('create_lead'))
                                            <a href="{{ route('admin.leads.create') }}" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4">
                                                <i class="fas fa-plus me-1 me-md-2"></i><span class="d-none d-sm-inline">Add New Lead</span><span class="d-sm-none">Add</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <!-- Filter Section -->
                                <div class="card border mb-4">
                                    <div class="card-body py-3">
                                        <form action="{{ route('admin.leads.index') }}" method="GET" id="filter-form">
                                            <div class="row g-2 g-md-3 align-items-end">
                                                <div class="col-6 col-md-3">
                                                    <label for="status" class="form-label fw-medium mb-1 small">Status</label>
                                                    <select name="status" id="status" class="form-select form-select-sm rounded-pill">
                                                        <option value="">All Statuses</option>
                                                        @foreach($statuses as $value => $label)
                                                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label for="from_date" class="form-label fw-medium mb-1 small">From Date</label>
                                                    <input type="date" name="from_date" id="from_date" class="form-control form-control-sm rounded-pill" value="{{ request('from_date') }}">
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <label for="to_date" class="form-label fw-medium mb-1 small">To Date</label>
                                                    <input type="date" name="to_date" id="to_date" class="form-control form-control-sm rounded-pill" value="{{ request('to_date') }}">
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="d-flex gap-2">
                                                        <button type="submit" class="btn btn-sm btn-theme rounded-pill px-3">
                                                            <i class="fas fa-filter me-1"></i><span class="d-none d-sm-inline">Filter</span>
                                                        </button>
                                                        <a href="{{ route('admin.leads.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                            <i class="fas fa-times me-1"></i><span class="d-none d-sm-inline">Reset</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Active Filters Display -->
                                @if(request('status') || request('from_date') || request('to_date'))
                                <div class="mb-3">
                                    <span class="text-muted me-2">Active Filters:</span>
                                    @if(request('status'))
                                        <span class="badge bg-primary rounded-pill px-3 py-2 me-1">
                                            Status: {{ $statuses[request('status')] ?? request('status') }}
                                        </span>
                                    @endif
                                    @if(request('from_date'))
                                        <span class="badge bg-info rounded-pill px-3 py-2 me-1">
                                            From: {{ \Carbon\Carbon::parse(request('from_date'))->format('M d, Y') }}
                                        </span>
                                    @endif
                                    @if(request('to_date'))
                                        <span class="badge bg-info rounded-pill px-3 py-2 me-1">
                                            To: {{ \Carbon\Carbon::parse(request('to_date'))->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                                @endif
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="leads-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>SR No.</th>
                                                <th>Name</th>
                                                <th>Contact Number</th>
                                                <th>Note</th>
                                                <th>Status</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $sr = 1;
                                            @endphp
                                            @foreach($leads as $lead)
                                            <tr>
                                                <td>{{ $sr++ }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <div class="fw-medium">{{ $lead->name }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $lead->contact_number }}</td>
                                                <td>{{ Str::limit($lead->note, 50) }}</td>
                                                <td>
                                                    <span class="badge {{ $lead->status_badge_class }} rounded-pill px-3 py-2">
                                                        {{ $lead->status_label }}
                                                    </span>
                                                </td>
                                                <td>{{ $lead->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if(auth()->user()->hasPermission('update_lead'))
                                                            <a href="{{ route('admin.leads.edit', $lead) }}" class="btn btn-outline-primary rounded-start-pill px-3" title="Edit Lead">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endif
                                                        @if(auth()->user()->hasPermission('delete_lead'))
                                                            <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST" class="d-inline delete-form">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" class="btn btn-outline-danger {{ auth()->user()->hasPermission('update_lead') ? '' : 'rounded-start-pill' }} rounded-end-pill px-3 delete-btn" title="Delete Lead">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
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
                <p class="mb-0">Are you sure you want to delete this lead?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Delete confirmation modal handling
        var deleteForm = null;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            deleteForm = $(this).closest('.delete-form');
            deleteModal.show();
        });
        
        $('#confirmDeleteBtn').on('click', function() {
            if (deleteForm) {
                deleteForm.submit();
            }
        });
        
        $('#leads-table').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "ordering": true,
            "searching": true,
            "info": true,
            "paging": true,
            "columnDefs": [
                { "orderable": false, "targets": [6] }
            ],
            "order": [[5, "desc"]], // Order by created_at by default
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ leads",
                "infoEmpty": "Showing 0 to 0 of 0 leads",
                "infoFiltered": "(filtered from _MAX_ total leads)",
                "emptyTable": '<div class="text-center py-4"><div class="text-muted"><i class="fas fa-user-tie fa-3x mb-3 d-block"></i><p class="mb-2">No leads found</p><p class="small mb-3">{{ request()->hasAny(["status", "from_date", "to_date"]) ? "Try adjusting your filters" : "Get started by creating a new lead" }}</p>@if(!request()->hasAny(["status", "from_date", "to_date"]))<a href="{{ route("admin.leads.create") }}" class="btn btn-theme btn-sm rounded-pill px-4"><i class="fas fa-plus me-2"></i>Add New Lead</a>@endif</div></div>',
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
