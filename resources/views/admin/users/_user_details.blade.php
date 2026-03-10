<div class="row">
    <div class="col-md-4 mb-4">
        <div class="text-center">
            <img src="{{ $user->avatar_url }}" 
                 class="rounded-circle mb-3" width="150" height="150" alt="{{ $user->name }}">
            <h4 class="mb-1">{{ $user->name }}</h4>
            <p class="text-muted mb-0">{{ $user->email }}</p>
            @if(Auth::user()->id == $user->id)
                <span class="badge bg-success-subtle text-success-emphasis rounded-pill mt-2">You</span>
            @endif
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Name</label>
                <p class="form-control-plaintext">{{ $user->name }}</p>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Email</label>
                <p class="form-control-plaintext">{{ $user->email }}</p>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Role</label>
                @php
                    $roleClass = [
                        'super_admin' => 'bg-danger-subtle text-danger-emphasis',
                        'admin' => 'bg-primary-subtle text-primary-emphasis',
                        'editor' => 'bg-warning-subtle text-warning-emphasis',
                        'user' => 'bg-secondary-subtle text-secondary-emphasis'
                    ][$user->user_role] ?? 'bg-secondary-subtle text-secondary-emphasis';
                @endphp
                <p class="form-control-plaintext">
                    <span class="badge {{ $roleClass }} rounded-pill px-3 py-2">
                        {{ ucfirst(str_replace('_', ' ', $user->user_role)) }}
                    </span>
                </p>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Date of Birth</label>
                <p class="form-control-plaintext">
                    @if($user->date_of_birth)
                        {{ $user->date_of_birth->format('F j, Y') }}
                    @else
                        <span class="text-muted">Not provided</span>
                    @endif
                </p>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Address</label>
                <p class="form-control-plaintext">
                    @if($user->address)
                        {{ $user->address }}
                    @else
                        <span class="text-muted">Not provided</span>
                    @endif
                </p>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Mobile Number</label>
                <p class="form-control-plaintext">
                    @if($user->mobile_number)
                        {{ $user->mobile_number }}
                    @else
                        <span class="text-muted">Not provided</span>
                    @endif
                </p>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Account Created</label>
                <p class="form-control-plaintext">
                    @if($user->created_at)
                        {{ $user->created_at->format('F j, Y \a\t g:i A') }}
                    @else
                        <span class="text-muted">Unknown</span>
                    @endif
                </p>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Last Updated</label>
                <p class="form-control-plaintext">
                    @if($user->updated_at)
                        {{ $user->updated_at->format('F j, Y \a\t g:i A') }}
                    @else
                        <span class="text-muted">Never</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-4">
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-theme rounded-pill px-4 me-2">
        <i class="fas fa-edit me-2"></i> Edit User
    </a>
    
    @if(Auth::user()->id != $user->id)
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger rounded-pill px-4">
                <i class="fas fa-trash me-2"></i> Delete User
            </button>
        </form>
    @endif
</div>