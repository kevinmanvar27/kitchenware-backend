@extends('admin.layouts.app')

@section('title', 'Permissions Management')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Permissions Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Permissions</h4>
                                    <p class="mb-0 text-muted">Manage system permissions</p>
                                </div>
                                <a href="{{ route('admin.permissions.create') }}" class="btn btn-theme rounded-pill px-4">
                                    <i class="fas fa-plus me-2"></i> Add New Permission
                                </a>
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
                                    <table class="table table-hover align-middle" id="permissionsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>SR No.</th>
                                                <th>Name</th>
                                                <th>Display Name</th>
                                                <th>Description</th>
                                                <th>Roles</th>
                                                <th>Users</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($permissions as $index => $permission)
                                                <tr>
                                                    <td class="fw-bold">{{ $index + 1 }}</td>
                                                    <td>
                                                        <span class="fw-medium">{{ $permission->name }}</span>
                                                    </td>
                                                    <td>{{ $permission->display_name }}</td>
                                                    <td>{{ $permission->description ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill">
                                                            {{ $permission->roles->count() }} roles
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">
                                                            {{ $permission->users->count() }} users
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-outline-primary rounded-start-pill px-3">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this permission? This action cannot be undone.');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger rounded-end-pill px-3">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-key fa-2x mb-3"></i>
                                                            <p class="mb-0">No permissions found</p>
                                                            <p class="small">Try creating a new permission</p>
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#permissionsTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "ordering": true,
            "searching": true,
            "info": true,
            "paging": true,
            "columnDefs": [
                { "orderable": false, "targets": [6] } // Disable sorting on Actions column
            ],
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ permissions",
                "infoEmpty": "Showing 0 to 0 of 0 permissions",
                "infoFiltered": "(filtered from _MAX_ total permissions)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "aoColumns": [
                null, // ID
                null, // Name
                null, // Display Name
                null, // Description
                null, // Roles
                null, // Users
                null  // Actions
            ],
            "preDrawCallback": function(settings) {
                // Ensure consistent column count
                if ($('#permissionsTable tbody tr').length === 0) {
                    $('#permissionsTable tbody').html('<tr><td colspan="7" class="text-center py-5"><div class="text-muted"><i class="fas fa-key fa-2x mb-3"></i><p class="mb-0">No permissions found</p><p class="small">Try creating a new permission</p></div></td></tr>');
                }
            }
        });
        // Adjust select width after DataTable initializes
        $('.dataTables_length select').css('width', '80px');
    });
</script>
@endsection
