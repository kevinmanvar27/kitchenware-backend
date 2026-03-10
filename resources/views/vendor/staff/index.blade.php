@extends('vendor.layouts.app')

@section('title', 'Staff Management')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Staff Management'])
            
            <div class="pt-4 pb-2 mb-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error') || $errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        @if(session('error'))
                            {{ session('error') }}
                        @else
                            {{ $errors->first() }}
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-0 fw-bold">Staff Members</h4>
                                <p class="text-muted mb-0 small">Manage your store staff</p>
                            </div>
                            <a href="{{ route('vendor.staff.create') }}" class="btn btn-theme rounded-pill px-4">
                                <i class="fas fa-plus me-2"></i>Add Staff
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Staff Member</th>
                                        <th>Role</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($staff as $member)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                        <span class="text-primary fw-bold">{{ strtoupper(substr($member->user->name, 0, 1)) }}</span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">{{ $member->user->name }}</div>
                                                        <small class="text-muted">{{ $member->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3 py-2">
                                                    {{ ucfirst($member->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($member->user->mobile_number)
                                                    <a href="tel:{{ $member->user->mobile_number }}" class="text-decoration-none">
                                                        {{ $member->user->mobile_number }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($member->is_active)
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                                        <i class="fas fa-check-circle me-1"></i>Active
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                                                        <i class="fas fa-times-circle me-1"></i>Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $member->created_at->format('d M Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('vendor.staff.edit', $member->id) }}" class="btn btn-outline-secondary rounded-start-pill px-3" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-{{ $member->is_active ? 'warning' : 'success' }} px-3" onclick="toggleStatus({{ $member->id }})" title="{{ $member->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas fa-{{ $member->is_active ? 'ban' : 'check' }}"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger rounded-end-pill px-3" onclick="deleteStaff({{ $member->id }})" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-users fa-3x mb-3"></i>
                                                    <p class="mb-0">No staff members found</p>
                                                    <p class="small">Add your first staff member to get started</p>
                                                    <a href="{{ route('vendor.staff.create') }}" class="btn btn-theme rounded-pill px-4 mt-2">
                                                        <i class="fas fa-plus me-2"></i>Add Staff
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($staff->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $staff->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
    const baseUrl = '{{ url('/') }}';
    
    function toggleStatus(id) {
        $.ajax({
            url: baseUrl + '/vendor/staff/' + id + '/toggle-status',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                alert('Error toggling status');
            }
        });
    }
    
    function deleteStaff(id) {
        if (confirm('Are you sure you want to delete this staff member? This action cannot be undone.')) {
            const form = document.getElementById('deleteForm');
            form.action = baseUrl + '/vendor/staff/' + id;
            form.submit();
        }
    }
</script>
@endsection
