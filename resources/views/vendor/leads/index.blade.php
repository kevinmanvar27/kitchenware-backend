@extends('vendor.layouts.app')

@section('title', 'Leads')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Lead Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Leads</h4>
                                        <p class="mb-0 text-muted small">Manage your leads</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('vendor.leads.reminders') }}" class="btn btn-sm btn-md-normal btn-outline-warning rounded-pill px-3 px-md-4">
                                            <i class="fas fa-bell me-1 me-md-2"></i><span class="d-none d-sm-inline">Reminders</span>
                                            @php
                                                $reminderVendorId = null;
                                                if (Auth::user()->vendor) {
                                                    $reminderVendorId = Auth::user()->vendor->id;
                                                } elseif (Auth::user()->vendorStaff && Auth::user()->vendorStaff->vendor) {
                                                    $reminderVendorId = Auth::user()->vendorStaff->vendor->id;
                                                }
                                                $dueRemindersCount = $reminderVendorId ? \App\Models\LeadReminder::where('vendor_id', $reminderVendorId)
                                                    ->where('status', 'pending')
                                                    ->where('reminder_at', '<=', now())
                                                    ->count() : 0;
                                            @endphp
                                            @if($dueRemindersCount > 0)
                                                <span class="badge bg-danger rounded-pill ms-1">{{ $dueRemindersCount }}</span>
                                            @endif
                                        </a>
                                        <a href="{{ route('vendor.leads.trashed') }}" class="btn btn-sm btn-md-normal btn-outline-secondary rounded-pill px-3 px-md-4">
                                            <i class="fas fa-trash-alt me-1 me-md-2"></i><span class="d-none d-sm-inline">Trashed</span>
                                        </a>
                                        <a href="{{ route('vendor.leads.create') }}" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4">
                                            <i class="fas fa-plus me-1 me-md-2"></i><span class="d-none d-sm-inline">Add New Lead</span><span class="d-sm-none">Add</span>
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
                                        <form action="{{ route('vendor.leads.index') }}" method="GET" id="filter-form">
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
                                                        <a href="{{ route('vendor.leads.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                            <i class="fas fa-times me-1"></i><span class="d-none d-sm-inline">Reset</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="leads-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>SR No.</th>
                                                <th>Name</th>
                                                <th>Contact Number</th>
                                                <th>Note</th>
                                                <th>Status</th>
                                                <th>Reminder</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $sr = 1; @endphp
                                            @foreach($leads as $lead)
                                            <tr>
                                                <td>{{ $sr++ }}</td>
                                                <td>
                                                    <div class="fw-medium">
                                                        <a href="{{ route('vendor.leads.show', $lead) }}" class="text-decoration-none">{{ $lead->name }}</a>
                                                    </div>
                                                </td>
                                                <td>{{ $lead->contact_number }}</td>
                                                <td>{{ Str::limit($lead->note, 50) }}</td>
                                                <td>
                                                    <span class="badge {{ $lead->status_badge_class }} rounded-pill px-3 py-2">
                                                        {{ $lead->status_label }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $nextReminder = $lead->nextReminder;
                                                    @endphp
                                                    @if($nextReminder)
                                                        <span class="badge {{ $nextReminder->is_overdue ? 'bg-danger' : 'bg-warning' }} rounded-pill px-2 py-1" 
                                                              title="{{ $nextReminder->title }} - {{ $nextReminder->reminder_at->format('M d, Y h:i A') }}">
                                                            <i class="fas fa-bell me-1"></i>
                                                            {{ $nextReminder->is_overdue ? 'Overdue' : $nextReminder->reminder_at->format('M d') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $lead->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('vendor.leads.show', $lead) }}" class="btn btn-outline-info rounded-start-pill px-3" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('vendor.leads.edit', $lead) }}" class="btn btn-outline-primary px-3" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('vendor.leads.destroy', $lead) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger rounded-end-pill px-3" onclick="return confirm('Are you sure you want to delete this lead?')" title="Delete">
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
                            @if($leads->hasPages())
                            <div class="card-footer bg-white border-0 py-3">
                                <div class="d-flex justify-content-end">
                                    {{ $leads->links() }}
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
        $('#leads-table').DataTable({
            "pageLength": 10,
            "ordering": true,
            "searching": true,
            "info": true,
            "paging": true,
            "columnDefs": [
                { "orderable": false, "targets": [7] }
            ],
            "order": [[6, "desc"]],
            "language": {
                "emptyTable": '<div class="text-center py-4"><div class="text-muted"><i class="fas fa-user-tie fa-2x mb-3 d-block"></i><p class="mb-0">No leads found</p></div></div>'
            }
        });
    });
</script>
@endsection