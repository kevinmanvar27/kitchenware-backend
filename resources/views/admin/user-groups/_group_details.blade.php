<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Group Name</label>
                <p class="form-control-plaintext">{{ $userGroup->name }}</p>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Discount Percentage</label>
                <p class="form-control-plaintext">
                    <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3 py-2">
                        {{ number_format($userGroup->discount_percentage, 2) }}%
                    </span>
                </p>
            </div>
            
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Description</label>
                <p class="form-control-plaintext">
                    @if($userGroup->description)
                        {{ $userGroup->description }}
                    @else
                        <span class="text-muted">No description provided</span>
                    @endif
                </p>
            </div>
            
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Members</label>
                @if($userGroup->users->count() > 0)
                    <div class="row">
                        @foreach($userGroup->users as $user)
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->avatar_url }}" 
                                         class="rounded-circle me-3" width="40" height="40" alt="{{ $user->name }}">
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
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="form-control-plaintext text-muted">No members in this group</p>
                @endif
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Created</label>
                <p class="form-control-plaintext">
                    @if($userGroup->created_at)
                        {{ $userGroup->created_at->format('F j, Y \a\t g:i A') }}
                    @else
                        <span class="text-muted">Unknown</span>
                    @endif
                </p>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Last Updated</label>
                <p class="form-control-plaintext">
                    @if($userGroup->updated_at)
                        {{ $userGroup->updated_at->format('F j, Y \a\t g:i A') }}
                    @else
                        <span class="text-muted">Never</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-4">
    <button type="button" class="btn btn-theme rounded-pill px-4 me-2" onclick="editUserGroup({{ $userGroup->id }})">
        <i class="fas fa-edit me-2"></i> Edit Group
    </button>
    
    <button type="button" class="btn btn-danger rounded-pill px-4" onclick="deleteUserGroup({{ $userGroup->id }})">
        <i class="fas fa-trash me-2"></i> Delete Group
    </button>
</div>