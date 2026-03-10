@extends('admin.layouts.app')

@section('title', 'Staff Management')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Staff Management',
                'breadcrumbs' => [
                    'Staff Management' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Staff Members</h4>
                                        <p class="mb-0 text-muted small">Manage administrative staff (Super Admins, Admins, Editors) and their salaries</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if(auth()->user()->hasPermission('viewAny_salary') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('admin.salary.index') }}" class="btn btn-sm btn-md-normal btn-outline-success rounded-pill px-3 px-md-4">
                                            <i class="fas fa-money-bill-wave me-1 me-md-2"></i><span class="d-none d-sm-inline">Salary Management</span><span class="d-sm-none">Salary</span>
                                        </a>
                                        @endif
                                        <a href="{{ route('admin.users.create', ['role' => 'staff']) }}" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4">
                                            <i class="fas fa-plus me-1 me-md-2"></i><span class="d-none d-sm-inline">Add New Staff</span><span class="d-sm-none">Add</span>
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
                                    <table class="table table-hover align-middle" id="staffTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Staff Member</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Address</th>
                                                <th>Mobile</th>
                                                <th>Date of Birth</th>
                                                <th>Salary</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($staff as $index => $user)
                                                <tr>
                                                    <td class="fw-bold">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $user->avatar_url }}" 
                                                                 class="rounded-circle me-3" width="40" height="40" alt="{{ $user->name }}">
                                                            <div>
                                                                <div class="fw-medium">{{ $user->name }}</div>
                                                                @if(Auth::user()->id == $user->id)
                                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">You</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        @php
                                                            $roleClass = [
                                                                'super_admin' => 'bg-danger-subtle text-danger-emphasis',
                                                                'admin' => 'bg-primary-subtle text-primary-emphasis',
                                                                'editor' => 'bg-warning-subtle text-warning-emphasis',
                                                                'user' => 'bg-secondary-subtle text-secondary-emphasis'
                                                            ][$user->user_role] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                                        @endphp
                                                        <span class="badge {{ $roleClass }} rounded-pill px-3 py-2">
                                                            {{ ucfirst(str_replace('_', ' ', $user->user_role)) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $user->address ?? 'N/A' }}</td>
                                                    <td>{{ $user->mobile_number ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($user->date_of_birth)
                                                            <span class="text-muted">{{ $user->date_of_birth->format('M d, Y') }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $activeSalary = $user->salaries()->where('is_active', true)->first();
                                                        @endphp
                                                        @if($activeSalary)
                                                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                                                ₹{{ number_format($activeSalary->base_salary, 2) }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">
                                                                Not Set
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button type="button" class="btn btn-outline-info rounded-start-pill px-3" data-user-id="{{ $user->id }}" onclick="showUserDetails(this.getAttribute('data-user-id'))">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <a href="{{ route('admin.salary.create', ['user_id' => $user->id]) }}" class="btn btn-outline-success px-3" title="Manage Salary">
                                                                <i class="fas fa-dollar-sign"></i>
                                                            </a>
                                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-secondary px-3">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            @if(Auth::user()->id != $user->id)
                                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this staff member? This action cannot be undone.');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-outline-danger rounded-end-pill px-3">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <button type="button" class="btn btn-outline-secondary rounded-end-pill px-3" disabled>
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                {{-- Handled by DataTables JavaScript --}}
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

<!-- Modal for showing user details -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Staff Member Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userModalBody">
                <!-- Content will be loaded here via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#staffTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "ordering": true,
            "searching": true,
            "info": true,
            "paging": true,
            "columnDefs": [
                { "orderable": false, "targets": [8] } // Disable sorting on Actions column
            ],
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ staff members",
                "infoEmpty": "Showing 0 to 0 of 0 staff members",
                "infoFiltered": "(filtered from _MAX_ total staff members)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "aoColumns": [
                null, // #
                null, // Staff Member
                null, // Email
                null, // Role
                null, // Address
                null, // Mobile
                null, // Date of Birth
                null, // Salary
                null  // Actions
            ],
            "preDrawCallback": function(settings) {
                // Ensure consistent column count
                if ($('#staffTable tbody tr').length === 0) {
                    $('#staffTable tbody').html('<tr><td colspan="9" class="text-center py-5"><div class="text-muted"><i class="fas fa-users fa-2x mb-3"></i><p class="mb-0">No staff members found</p><p class="small">Try creating a new staff member</p></div></td></tr>');
                }
            }
        });
        // Adjust select width after DataTable initializes
        $('.dataTables_length select').css('width', '80px');
    });
    
    // Function to show user details in modal
    function showUserDetails(userId) {
        $.ajax({
            url: '/admin/users/' + userId,
            type: 'GET',
            success: function(data) {
                // Set the modal body content
                $('#userModalBody').html(data);
                
                // Show the modal
                $('#userModal').modal('show');
            },
            error: function() {
                alert('Error loading user details.');
            }
        });
    }
</script>
@endsection