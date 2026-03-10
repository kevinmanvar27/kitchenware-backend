@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'User Details',
                'breadcrumbs' => [
                    'Users' => route('admin.users.index'),
                    'User Details' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">User Information</h4>
                                    <p class="mb-0 text-muted">Detailed information about the user</p>
                                </div>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Users
                                </a>
                            </div>
                            
                            <div class="card-body">
                                @include('admin.users._user_details', ['user' => $user])
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
                <h5 class="modal-title" id="userModalLabel">User Details</h5>
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
    // Function to show user details in modal
    function showUserDetails(userId) {
        $.ajax({
            url: '/admin/users/' + userId,
            type: 'GET',
            success: function(data) {
                $('#userModalBody').html(data);
                $('#userModal').modal('show');
            },
            error: function() {
                alert('Error loading user details.');
            }
        });
    }
</script>
@endsection