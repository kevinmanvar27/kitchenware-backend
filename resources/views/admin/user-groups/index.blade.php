@extends('admin.layouts.app')

@section('title', 'User Groups')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'User Group Management',
                'breadcrumbs' => [
                    'User Groups' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">User Groups</h4>
                                        <p class="mb-0 text-muted small">Manage user groups and their members</p>
                                    </div>
                                    <a href="{{ route('admin.user-groups.create') }}" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4">
                                        <i class="fas fa-plus me-1 me-md-2"></i><span class="d-none d-sm-inline">Add New Group</span><span class="d-sm-none">Add</span>
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
                                    <table class="table table-hover align-middle" id="userGroupsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Group Name</th>
                                                <th>Description</th>
                                                <th>Discount %</th>
                                                <th>Members</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($userGroups as $index => $group)
                                                <tr>
                                                    <td class="fw-bold">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="fw-medium">{{ $group->name }}</div>
                                                    </td>
                                                    <td>
                                                        @if($group->description)
                                                            <span class="text-muted">{{ Str::limit($group->description, 50) }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3 py-2">
                                                            {{ number_format($group->discount_percentage, 2) }}%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                                                            {{ $group->users->count() }} members
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button type="button" class="btn btn-outline-info rounded-start-pill px-3" onclick="showUserGroupDetails({{ $group->id }})">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary px-3" onclick="editUserGroup({{ $group->id }})">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger rounded-end-pill px-3" onclick="deleteUserGroup({{ $group->id }})">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-users fa-2x mb-3"></i>
                                                            <p class="mb-0">No user groups found</p>
                                                            <p class="small">Try creating a new user group</p>
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

<!-- Modal for showing user group details -->
<div class="modal fade" id="userGroupModal" tabindex="-1" aria-labelledby="userGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userGroupModalLabel">User Group Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userGroupModalBody">
                <!-- Content will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Wait for the document to be ready and jQuery to be available
    function initializeUserGroupsPage() {
        $(document).ready(function() {
            // Initialize DataTable
            $('#userGroupsTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "ordering": true,
                "searching": true,
                "info": true,
                "paging": true,
                "columnDefs": [
                    { "orderable": false, "targets": [5] } // Disable sorting on Actions column
                ],
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ user groups",
                    "infoEmpty": "Showing 0 to 0 of 0 user groups",
                    "infoFiltered": "(filtered from _MAX_ total user groups)",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                },
                "aoColumns": [
                    null, // #
                    null, // Group Name
                    null, // Description
                    null, // Discount %
                    null, // Members
                    null  // Actions
                ],
                "preDrawCallback": function(settings) {
                    // Ensure consistent column count
                    if ($('#userGroupsTable tbody tr').length === 0) {
                        $('#userGroupsTable tbody').html('<tr><td colspan="6" class="text-center py-5"><div class="text-muted"><i class="fas fa-users fa-2x mb-3"></i><p class="mb-0">No user groups found</p><p class="small">Try creating a new user group</p></div></td></tr>');
                    }
                }
            });
            // Adjust select width after DataTable initializes
            $('.dataTables_length select').css('width', '80px');
        });
    }
    
    // Function to show user group details in modal
    function showUserGroupDetails(userGroupId) {
        $.ajax({
            url: '/admin/user-groups/' + userGroupId,
            type: 'GET',
            success: function(data) {
                // Set the modal title
                $('#userGroupModalLabel').text('User Group Details');
                
                // Set the modal body content
                $('#userGroupModalBody').html(data);
                
                // Show the modal
                $('#userGroupModal').modal('show');
            },
            error: function() {
                showAlert('error', 'Error loading user group details.');
            }
        });
    }
    
    // Function to edit user group in modal
    function editUserGroup(userGroupId) {
        $.ajax({
            url: '/admin/user-groups/' + userGroupId + '/edit',
            type: 'GET',
            success: function(data) {
                // Set the modal title
                $('#userGroupModalLabel').text('Edit User Group');
                
                // Set the modal body content
                $('#userGroupModalBody').html(data);
                
                // Show the modal
                $('#userGroupModal').modal('show');
            },
            error: function() {
                showAlert('error', 'Error loading user group edit form.');
            }
        });
    }
    
    // Function to delete user group
    function deleteUserGroup(userGroupId) {
        if (confirm('Are you sure you want to delete this user group? This action cannot be undone.')) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $.ajax({
                url: '/admin/user-groups/' + userGroupId,
                type: 'POST',
                data: {
                    '_method': 'DELETE'
                },
                success: function(response) {
                    // Show success message
                    showAlert('success', 'User group deleted successfully.');
                    
                    // Reload the page to reflect changes
                    location.reload();
                },
                error: function() {
                    showAlert('error', 'Error deleting user group.');
                }
            });
        }
    }
    
    // Function to show alerts
    function showAlert(type, message) {
        let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        let iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        let alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                <i class="fas ${iconClass} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Remove any existing alerts
        $('.alert').remove();
        
        // Add the new alert to the card body
        $('.card-body').prepend(alertHtml);
    }
    
    // Initialize the page once jQuery is available
    if (typeof jQuery !== 'undefined') {
        initializeUserGroupsPage();
    } else {
        // If jQuery isn't available yet, wait for it
        const checkjQuery = setInterval(function() {
            if (typeof jQuery !== 'undefined') {
                clearInterval(checkjQuery);
                initializeUserGroupsPage();
            }
        }, 100);
    }
</script>
@endsection
