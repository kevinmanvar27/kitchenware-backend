@extends('admin.layouts.app')

@section('title', 'Create User Group')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Create User Group',
                'breadcrumbs' => [
                    'User Groups' => route('admin.user-groups.index'),
                    'Create' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Create New User Group</h4>
                                    <p class="mb-0 text-muted">Define a new user group with members</p>
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
                                
                                <form action="{{ route('admin.user-groups.store') }}" method="POST" id="createUserGroupForm">
                                    @csrf
                                    <input type="hidden" name="force_transfer" id="force_transfer" value="0">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label fw-bold">Group Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4 py-2 @error('name') is-invalid @enderror" 
                                                       id="name" name="name" value="{{ old('name') }}" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="description" class="form-label fw-bold">Description</label>
                                                <textarea class="form-control rounded-3 px-4 py-2 @error('description') is-invalid @enderror" 
                                                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="discount_percentage" class="form-label fw-bold">Discount Percentage <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control rounded-start-pill px-4 py-2 @error('discount_percentage') is-invalid @enderror" 
                                                           id="discount_percentage" name="discount_percentage" 
                                                           value="{{ old('discount_percentage', 0) }}" 
                                                           min="0" max="100" step="0.01" required>
                                                    <span class="input-group-text rounded-end-pill">%</span>
                                                </div>
                                                @error('discount_percentage')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Select Users</label>
                                                <div class="card border rounded-3">
                                                    <div class="card-header bg-light py-2 px-3">
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white border-0 rounded-pill">
                                                                <i class="fas fa-search"></i>
                                                            </span>
                                                            <input type="text" class="form-control border-0 rounded-pill" 
                                                                   id="user-search" placeholder="Search users...">
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                                                        <div class="list-group list-group-flush" id="user-list">
                                                            @foreach($users as $user)
                                                                <div class="list-group-item border-0 px-3 py-2 user-item" 
                                                                     data-user-name="{{ strtolower($user->name) }}"
                                                                     data-user-id="{{ $user->id }}"
                                                                     data-has-group="{{ $user->userGroups->count() > 0 ? 'true' : 'false' }}"
                                                                     data-group-name="{{ $user->userGroups->count() > 0 ? $user->userGroups->first()->name : '' }}">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input user-checkbox" type="checkbox" 
                                                                               id="user_{{ $user->id }}" name="users[]" 
                                                                               value="{{ $user->id }}">
                                                                        <label class="form-check-label d-flex align-items-center" for="user_{{ $user->id }}">
                                                                            <img src="{{ $user->avatar_url }}" 
                                                                                 class="rounded-circle me-3" width="30" height="30" alt="{{ $user->name }}">
                                                                            <div>
                                                                                <div class="fw-medium">
                                                                                    {{ $user->name }} 
                                                                                    <span class="text-muted">
                                                                                        @if($user->mobile_number)
                                                                                            ({{ $user->mobile_number }})
                                                                                        @else
                                                                                            (No phone number)
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                                <div class="small">
                                                                                    @if($user->userGroups->count() > 0)
                                                                                        <span class="badge bg-info">Group: {{ $user->userGroups->first()->name }}</span>
                                                                                    @else
                                                                                        <span class="badge bg-secondary">No group assigned</span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Selected Users</label>
                                                <div class="card border rounded-3">
                                                    <div class="card-body p-3">
                                                        <div id="selected-users-container">
                                                            <div class="text-center py-5" id="no-selected-users">
                                                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                                                <p class="text-muted mb-0">No users selected</p>
                                                                <p class="small text-muted">Select users from the list above</p>
                                                            </div>
                                                            <div id="selected-users-list" class="d-none"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="{{ route('admin.user-groups.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                                            <i class="fas fa-arrow-left me-1"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4 py-2">
                                            <i class="fas fa-save me-1"></i> Save User Group
                                        </button>
                                    </div>
                                </form>
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
        let usersInOtherGroups = [];
        
        // Handle user search
        $('#user-search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            if (searchTerm === '') {
                $('.user-item').show();
            } else {
                $('.user-item').each(function() {
                    const userName = $(this).data('user-name');
                    if (userName.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
        
        // Handle user selection with conflict check
        $('.user-checkbox').on('change', function() {
            const userId = $(this).val();
            const userItem = $(this).closest('.user-item');
            const userName = userItem.find('.fw-medium').text().trim();
            const userAvatar = userItem.find('img').attr('src');
            const hasGroup = userItem.data('has-group') === 'true' || userItem.data('has-group') === true;
            const existingGroupName = userItem.data('group-name');
            const checkbox = $(this);
            
            if ($(this).is(':checked')) {
                // Check if user is already in another group
                if (hasGroup && existingGroupName) {
                    // User is already in a group - show warning
                    
                    // Store the conflict info
                    usersInOtherGroups.push({
                        user_id: userId,
                        user_name: userName,
                        existing_group: existingGroupName
                    });
                    
                    // Show warning modal
                    showConflictWarning(userId, userName, existingGroupName, userAvatar, checkbox);
                } else {
                    // Add to selected users normally
                    addUserToSelected(userId, userName, userAvatar);
                }
            } else {
                // Remove from selected users
                removeUserFromSelected(userId);
                // Remove from conflicts list
                usersInOtherGroups = usersInOtherGroups.filter(u => u.user_id != userId);
            }
            
            updateSelectedUsersDisplay();
        });
        
        // Show conflict warning modal
        function showConflictWarning(userId, userName, existingGroupName, userAvatar, checkbox) {
            const modalHtml = `
                <div class="modal fade" id="conflictWarningModal" tabindex="-1" aria-labelledby="conflictWarningModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title" id="conflictWarningModalLabel">
                                    <i class="fas fa-exclamation-triangle me-2"></i>User Already in Another Group
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <img src="${userAvatar}" class="rounded-circle mb-3" width="60" height="60" alt="${userName}">
                                    <h6 class="fw-bold">${userName}</h6>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle me-2"></i>
                                    This user is already assigned to the group: <strong>${existingGroupName}</strong>
                                </div>
                                <p class="mb-0">If you add this user to the new group, they will be automatically removed from <strong>${existingGroupName}</strong>.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-pill px-4 cancel-transfer-btn" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                                <button type="button" class="btn btn-warning rounded-pill px-4 proceed-transfer-btn" data-bs-dismiss="modal">
                                    <i class="fas fa-exchange-alt me-1"></i> Transfer User
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            $('#conflictWarningModal').remove();
            
            // Append modal to body
            $('body').append(modalHtml);
            const modalElement = document.getElementById('conflictWarningModal');
            const modal = new bootstrap.Modal(modalElement);
            
            // Handle proceed button
            $('#conflictWarningModal .proceed-transfer-btn').on('click', function() {
                console.log('User confirmed transfer for user ID:', userId);
                // User confirmed - add to selected users
                addUserToSelected(userId, userName, userAvatar);
                updateSelectedUsersDisplay();
                modal.hide();
                
                // Remove modal after hiding
                setTimeout(function() {
                    $('#conflictWarningModal').remove();
                }, 300);
            });
            
            // Handle cancel button
            $('#conflictWarningModal .cancel-transfer-btn').on('click', function() {
                console.log('User cancelled transfer for user ID:', userId);
                // User cancelled - uncheck the checkbox
                checkbox.prop('checked', false);
                // Remove from conflicts list
                usersInOtherGroups = usersInOtherGroups.filter(u => u.user_id != userId);
                modal.hide();
                
                // Remove modal after hiding
                setTimeout(function() {
                    $('#conflictWarningModal').remove();
                }, 300);
            });
            
            // Handle modal close (X button or backdrop)
            $(modalElement).on('hidden.bs.modal', function() {
                // If neither button was clicked, treat as cancel
                if (checkbox.is(':checked') && $(`.selected-user[data-user-id="${userId}"]`).length === 0) {
                    console.log('Modal closed without action - treating as cancel');
                    checkbox.prop('checked', false);
                    usersInOtherGroups = usersInOtherGroups.filter(u => u.user_id != userId);
                }
                
                // Clean up
                setTimeout(function() {
                    $('#conflictWarningModal').remove();
                }, 300);
            });
            
            // Show modal
            modal.show();
        }
        
        // Add user to selected users list
        function addUserToSelected(userId, userName, userAvatar) {
            const userHtml = `
                <div class="d-inline-block me-2 mb-2 selected-user" data-user-id="${userId}">
                    <div class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                        <img src="${userAvatar}" class="rounded-circle me-2" width="20" height="20" alt="${userName}">
                        ${userName}
                        <button type="button" class="btn-close btn-close-white ms-2 remove-user-btn" data-user-id="${userId}"></button>
                    </div>
                </div>
            `;
            
            $('#selected-users-list').append(userHtml);
        }
        
        // Remove user from selected users list
        function removeUserFromSelected(userId) {
            $(`.selected-user[data-user-id="${userId}"]`).remove();
        }
        
        // Update selected users display
        function updateSelectedUsersDisplay() {
            if ($('#selected-users-list').children().length > 0) {
                $('#no-selected-users').addClass('d-none');
                $('#selected-users-list').removeClass('d-none');
            } else {
                $('#no-selected-users').removeClass('d-none');
                $('#selected-users-list').addClass('d-none');
            }
        }
        
        // Handle remove user button click
        $(document).on('click', '.remove-user-btn', function() {
            const userId = $(this).data('user-id');
            
            // Uncheck the checkbox
            $(`#user_${userId}`).prop('checked', false);
            
            // Remove from selected users
            removeUserFromSelected(userId);
            
            // Remove from conflicts list
            usersInOtherGroups = usersInOtherGroups.filter(u => u.user_id != userId);
            
            updateSelectedUsersDisplay();
        });
        
        // Handle form submission
        $('#createUserGroupForm').on('submit', function(e) {
            // If there are users in other groups, set force_transfer to 1
            if (usersInOtherGroups.length > 0) {
                console.log('Setting force_transfer to 1 for users:', usersInOtherGroups);
                $('#force_transfer').val('1');
            } else {
                console.log('No conflicts, setting force_transfer to 0');
                $('#force_transfer').val('0');
            }
            
            // Log form data for debugging
            console.log('Form data:', $(this).serialize());
            
            // Allow form to submit normally
            return true;
        });
    });
</script>
@endsection