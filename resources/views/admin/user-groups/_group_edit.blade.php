<form id="editUserGroupForm" action="{{ route('admin.user-groups.update', $userGroup) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="force_transfer" id="force_transfer" value="0">
    
    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label for="name" class="form-label fw-bold">Group Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control rounded-pill px-4 py-2 @error('name') is-invalid @enderror" 
                       id="name" name="name" value="{{ old('name', $userGroup->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label fw-bold">Description</label>
                <textarea class="form-control rounded-3 px-4 py-2 @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3">{{ old('description', $userGroup->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="discount_percentage" class="form-label fw-bold">Discount Percentage <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" class="form-control rounded-start-pill px-4 py-2 @error('discount_percentage') is-invalid @enderror" 
                           id="discount_percentage" name="discount_percentage" 
                           value="{{ old('discount_percentage', $userGroup->discount_percentage) }}" 
                           min="0" max="100" step="0.01" required>
                    <span class="input-group-text rounded-end-pill">%</span>
                </div>
                @error('discount_percentage')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
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
                                               value="{{ $user->id }}"
                                               {{ in_array($user->id, old('users', $selectedUsers)) ? 'checked' : '' }}>
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
                            <div class="text-center py-5 {{ count(old('users', $selectedUsers)) > 0 ? 'd-none' : '' }}" id="no-selected-users">
                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No users selected</p>
                                <p class="small text-muted">Select users from the list above</p>
                            </div>
                            <div id="selected-users-list" class="{{ count(old('users', $selectedUsers)) > 0 ? '' : 'd-none' }}">
                                @foreach($users as $user)
                                    @if(in_array($user->id, old('users', $selectedUsers)))
                                        <div class="d-inline-block me-2 mb-2 selected-user" data-user-id="{{ $user->id }}">
                                            <div class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                                                <img src="{{ $user->avatar_url }}" class="rounded-circle me-2" width="20" height="20" alt="{{ $user->name }}">
                                                {{ $user->name }}
                                                <button type="button" class="btn-close btn-close-white ms-2 remove-user-btn" data-user-id="{{ $user->id }}"></button>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i> Cancel
        </button>
        <button type="submit" class="btn btn-theme rounded-pill px-4 py-2">
            <i class="fas fa-save me-1"></i> Update User Group
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    let usersInOtherGroups = [];
    const currentGroupId = {{ $userGroup->id }};
    
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
        const currentGroupName = '{{ $userGroup->name }}';
        const checkbox = $(this);
        
        if ($(this).is(':checked')) {
            // Check if user is already in another group (not the current one)
            if (hasGroup && existingGroupName && existingGroupName !== currentGroupName) {
                // User is already in a different group - show warning
                
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
            <div class="modal fade" id="conflictWarningModalEdit" tabindex="-1" aria-labelledby="conflictWarningModalEditLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title" id="conflictWarningModalEditLabel">
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
                            <p class="mb-0">If you add this user to this group, they will be automatically removed from <strong>${existingGroupName}</strong>.</p>
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
        $('#conflictWarningModalEdit').remove();
        
        // Append modal to body
        $('body').append(modalHtml);
        const modalElement = document.getElementById('conflictWarningModalEdit');
        const modal = new bootstrap.Modal(modalElement);
        
        // Handle proceed button
        $('#conflictWarningModalEdit .proceed-transfer-btn').on('click', function() {
            console.log('User confirmed transfer for user ID:', userId);
            // User confirmed - add to selected users
            addUserToSelected(userId, userName, userAvatar);
            updateSelectedUsersDisplay();
            modal.hide();
            
            // Remove modal after hiding
            setTimeout(function() {
                $('#conflictWarningModalEdit').remove();
            }, 300);
        });
        
        // Handle cancel button
        $('#conflictWarningModalEdit .cancel-transfer-btn').on('click', function() {
            console.log('User cancelled transfer for user ID:', userId);
            // User cancelled - uncheck the checkbox
            checkbox.prop('checked', false);
            // Remove from conflicts list
            usersInOtherGroups = usersInOtherGroups.filter(u => u.user_id != userId);
            modal.hide();
            
            // Remove modal after hiding
            setTimeout(function() {
                $('#conflictWarningModalEdit').remove();
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
                $('#conflictWarningModalEdit').remove();
            }, 300);
        });
        
        // Show modal
        modal.show();
    }
    
    // Add user to selected users list
    function addUserToSelected(userId, userName, userAvatar) {
        // Check if user is already in the selected list
        if ($(`.selected-user[data-user-id="${userId}"]`).length > 0) {
            return;
        }
        
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
    $('#editUserGroupForm').on('submit', function(e) {
        e.preventDefault();
        
        // If there are users in other groups, set force_transfer to 1
        if (usersInOtherGroups.length > 0) {
            console.log('Setting force_transfer to 1 for users:', usersInOtherGroups);
            $('#force_transfer').val('1');
        } else {
            console.log('No conflicts, setting force_transfer to 0');
            $('#force_transfer').val('0');
        }
        
        const form = $(this);
        const url = form.attr('action');
        const formData = form.serialize();
        
        console.log('Submitting form with data:', formData);
        
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: {
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(response) {
                console.log('Form submitted successfully:', response);
                // Close the modal
                $('#userGroupModal').modal('hide');
                
                // Show success message
                showAlert('success', 'User group updated successfully.');
                
                // Reload the page to reflect changes
                setTimeout(function() {
                    location.reload();
                }, 500);
            },
            error: function(xhr) {
                console.error('Form submission error:', xhr);
                // Handle validation errors
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    
                    for (const field in errors) {
                        errorMessages += errors[field][0] + '<br>';
                    }
                    
                    showAlert('error', errorMessages);
                } else {
                    showAlert('error', 'An error occurred while updating the user group.');
                }
            }
        });
    });
    
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
});
</script>