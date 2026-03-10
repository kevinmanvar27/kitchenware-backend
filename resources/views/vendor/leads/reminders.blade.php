@extends('vendor.layouts.app')

@section('title', 'Lead Reminders')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Lead Reminders'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">
                                            <i class="fas fa-bell me-2"></i>Reminders
                                            @if($dueCount > 0)
                                                <span class="badge bg-danger rounded-pill ms-2">{{ $dueCount }} Due</span>
                                            @endif
                                        </h4>
                                        <p class="mb-0 text-muted small">Manage your lead reminders</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('vendor.leads.index') }}" class="btn btn-sm btn-md-normal btn-outline-secondary rounded-pill px-3 px-md-4">
                                            <i class="fas fa-arrow-left me-1 me-md-2"></i><span class="d-none d-sm-inline">Back to Leads</span>
                                        </a>
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
                                        <form action="{{ route('vendor.leads.reminders') }}" method="GET" id="filter-form">
                                            <div class="row g-2 g-md-3 align-items-end">
                                                <div class="col-6 col-md-3">
                                                    <label for="status" class="form-label fw-medium mb-1 small">Status</label>
                                                    <select name="status" id="status" class="form-select form-select-sm rounded-pill">
                                                        <option value="">All Statuses</option>
                                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                                        <option value="dismissed" {{ request('status') == 'dismissed' ? 'selected' : '' }}>Dismissed</option>
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
                                                        <a href="{{ route('vendor.leads.reminders') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                            <i class="fas fa-times me-1"></i><span class="d-none d-sm-inline">Reset</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="reminders-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>SR No.</th>
                                                <th>Title</th>
                                                <th>Lead</th>
                                                <th>Reminder Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $sr = 1; @endphp
                                            @foreach($reminders as $reminder)
                                            <tr class="{{ $reminder->is_overdue ? 'table-danger' : '' }}">
                                                <td>{{ $sr++ }}</td>
                                                <td>
                                                    <div class="fw-medium">{{ $reminder->title }}</div>
                                                    @if($reminder->description)
                                                        <small class="text-muted">{{ Str::limit($reminder->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('vendor.leads.show', $reminder->lead) }}" class="text-decoration-none">
                                                        {{ $reminder->lead->name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <div>{{ $reminder->reminder_at->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ $reminder->reminder_at->format('h:i A') }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $reminder->status_badge_class }} rounded-pill px-3 py-2">
                                                        {{ $reminder->status_label }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if($reminder->status === 'pending')
                                                            <form action="{{ route('vendor.leads.reminders.complete', $reminder) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-outline-success rounded-start-pill px-3" title="Mark Complete">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                            <form action="{{ route('vendor.leads.reminders.dismiss', $reminder) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-outline-secondary px-3" title="Dismiss">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <form action="{{ route('vendor.leads.reminders.destroy', $reminder) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger {{ $reminder->status !== 'pending' ? 'rounded-pill' : 'rounded-end-pill' }} px-3" onclick="return confirm('Are you sure you want to delete this reminder?')" title="Delete">
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
                            </div>
                            @if($reminders->hasPages())
                            <div class="card-footer bg-white border-0 py-3">
                                <div class="d-flex justify-content-end">
                                    {{ $reminders->links() }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#reminders-table').DataTable({
            "pageLength": 15,
            "ordering": true,
            "searching": true,
            "info": true,
            "paging": false,
            "columnDefs": [
                { "orderable": false, "targets": [5] }
            ],
            "order": [[3, "asc"]],
            "language": {
                "emptyTable": '<div class="text-center py-4"><div class="text-muted"><i class="fas fa-bell-slash fa-2x mb-3 d-block"></i><p class="mb-0">No reminders found</p></div></div>'
            }
        });
    });
</script>
@endsection