@extends('vendor.layouts.app')

@section('title', 'Trashed Leads')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Trashed Leads'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Trashed Leads</h4>
                                        <p class="mb-0 text-muted small">Manage your deleted leads</p>
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
                                
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="trashed-leads-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>SR No.</th>
                                                <th>Name</th>
                                                <th>Contact Number</th>
                                                <th>Note</th>
                                                <th>Status</th>
                                                <th>Deleted At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $sr = 1; @endphp
                                            @foreach($leads as $lead)
                                            <tr>
                                                <td>{{ $sr++ }}</td>
                                                <td>
                                                    <div class="fw-medium">{{ $lead->name }}</div>
                                                </td>
                                                <td>{{ $lead->contact_number }}</td>
                                                <td>{{ Str::limit($lead->note, 50) }}</td>
                                                <td>
                                                    <span class="badge {{ $lead->status_badge_class }} rounded-pill px-3 py-2">
                                                        {{ $lead->status_label }}
                                                    </span>
                                                </td>
                                                <td>{{ $lead->deleted_at->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <form action="{{ route('vendor.leads.restore', $lead->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-success rounded-start-pill px-3" onclick="return confirm('Are you sure you want to restore this lead?')">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('vendor.leads.force-delete', $lead->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger rounded-end-pill px-3" onclick="return confirm('Are you sure you want to permanently delete this lead? This action cannot be undone.')">
                                                                <i class="fas fa-trash-alt"></i>
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
        $('#trashed-leads-table').DataTable({
            "pageLength": 10,
            "ordering": true,
            "searching": true,
            "info": true,
            "paging": true,
            "columnDefs": [
                { "orderable": false, "targets": [6] }
            ],
            "order": [[5, "desc"]],
            "language": {
                "emptyTable": '<div class="text-center py-4"><div class="text-muted"><i class="fas fa-trash fa-2x mb-3 d-block"></i><p class="mb-0">No trashed leads found</p></div></div>'
            }
        });
    });
</script>
@endsection