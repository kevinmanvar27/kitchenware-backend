@extends('admin.layouts.app')

@section('title', 'Trashed Leads')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Trashed Leads'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Trashed Leads</h4>
                                    <p class="mb-0 text-muted">Manage deleted leads</p>
                                </div>
                                <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Leads
                                </a>
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
                                        <form action="{{ route('admin.leads.trashed') }}" method="GET" id="filter-form">
                                            <div class="row g-3 align-items-end">
                                                <div class="col-md-3">
                                                    <label for="status" class="form-label fw-medium mb-1">Status</label>
                                                    <select name="status" id="status" class="form-select rounded-pill">
                                                        <option value="">All Statuses</option>
                                                        @foreach($statuses as $value => $label)
                                                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="from_date" class="form-label fw-medium mb-1">Deleted From</label>
                                                    <input type="date" name="from_date" id="from_date" class="form-control rounded-pill" value="{{ request('from_date') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="to_date" class="form-label fw-medium mb-1">Deleted To</label>
                                                    <input type="date" name="to_date" id="to_date" class="form-control rounded-pill" value="{{ request('to_date') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="d-flex gap-2">
                                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                                            <i class="fas fa-filter me-2"></i>Filter
                                                        </button>
                                                        <a href="{{ route('admin.leads.trashed') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                                            <i class="fas fa-times me-2"></i>Reset
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
                                            Deleted From: {{ \Carbon\Carbon::parse(request('from_date'))->format('M d, Y') }}
                                        </span>
                                    @endif
                                    @if(request('to_date'))
                                        <span class="badge bg-info rounded-pill px-3 py-2 me-1">
                                            Deleted To: {{ \Carbon\Carbon::parse(request('to_date'))->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                                @endif
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="trashed-leads-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Contact Number</th>
                                                <th>Status</th>
                                                <th>Deleted At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($leads as $lead)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <div class="fw-medium">{{ $lead->name }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $lead->contact_number }}</td>
                                                <td>
                                                    <span class="badge {{ $lead->status_badge_class }} rounded-pill px-3 py-2">
                                                        {{ $lead->status_label }}
                                                    </span>
                                                </td>
                                                <td>{{ $lead->deleted_at->format('M d, Y h:i A') }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if(auth()->user()->hasPermission('update_lead'))
                                                            <form action="{{ route('admin.leads.restore', $lead->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-outline-success rounded-start-pill px-3" onclick="return confirm('Are you sure you want to restore this lead?')">
                                                                    <i class="fas fa-undo"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        @if(auth()->user()->hasPermission('delete_lead'))
                                                            <form action="{{ route('admin.leads.force-delete', $lead->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger {{ auth()->user()->hasPermission('update_lead') ? '' : 'rounded-start-pill' }} rounded-end-pill px-3" onclick="return confirm('Are you sure you want to permanently delete this lead? This action cannot be undone.')">
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#trashed-leads-table').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "ordering": true,
            "searching": true,
            "info": true,
            "paging": true,
            "columnDefs": [
                { "orderable": false, "targets": [4] }
            ],
            "order": [[3, "desc"]], // Order by deleted_at by default
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ trashed leads",
                "infoEmpty": "Showing 0 to 0 of 0 trashed leads",
                "infoFiltered": "(filtered from _MAX_ total trashed leads)",
                "emptyTable": '<div class="text-center py-4"><div class="text-muted"><i class="fas fa-trash-alt fa-2x mb-3 d-block"></i><p class="mb-0">No trashed leads found</p><p class="small">{{ request()->hasAny(["status", "from_date", "to_date"]) ? "Try adjusting your filters" : "Deleted leads will appear here" }}</p></div></div>',
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
