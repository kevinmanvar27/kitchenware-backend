@extends('vendor.layouts.app')

@section('title', 'My Tasks')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'My Tasks'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">Total Tasks</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm border-start border-warning border-3">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">Pending</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm border-start border-info border-3">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold text-info">{{ $stats['in_progress'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">In Progress</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm border-start border-danger border-3">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold text-danger">{{ $stats['with_questions'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">Questions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm border-start border-primary border-3">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold text-primary">{{ $stats['done'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">Done</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm border-start border-success border-3">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold text-success">{{ $stats['verified'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">Verified</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">My Assigned Tasks</h4>
                                    <p class="mb-0 text-muted">View and manage tasks assigned to you</p>
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

                                <!-- Filters -->
                                <form method="GET" action="{{ route('vendor.tasks.index') }}" class="mb-4">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <input type="text" name="search" class="form-control rounded-pill" placeholder="Search tasks..." value="{{ request('search') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <select name="status" id="filter_status" class="form-select rounded-pill select2" data-placeholder="All Status">
                                                <option value="">All Status</option>
                                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                <option value="question" {{ request('status') == 'question' ? 'selected' : '' }}>Question</option>
                                                <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary rounded-pill w-100">
                                                <i class="fas fa-filter me-2"></i>Filter
                                            </button>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="{{ route('vendor.tasks.index') }}" class="btn btn-outline-secondary rounded-pill w-100">
                                                <i class="fas fa-redo me-2"></i>Reset
                                            </a>
                                        </div>
                                    </div>
                                </form>

                                <!-- Tasks Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Assigned By</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($tasks as $task)
                                                <tr>
                                                    <td>{{ $task->id }}</td>
                                                    <td>
                                                        <strong>{{ $task->title }}</strong>
                                                        @if($task->attachment)
                                                            <br><small class="text-muted"><i class="fas fa-paperclip"></i> Has attachment</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($task->assignedBy)
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                                    {{ strtoupper(substr($task->assignedBy->name, 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <div class="fw-medium">{{ $task->assignedBy->name }}</div>
                                                                    <small class="text-muted">{{ $task->assignedBy->email }}</small>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusColors = [
                                                                'pending' => 'warning',
                                                                'in_progress' => 'info',
                                                                'question' => 'danger',
                                                                'done' => 'primary',
                                                                'verified' => 'success'
                                                            ];
                                                            $color = $statusColors[$task->status] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $color }} rounded-pill px-3 py-2">
                                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div>{{ $task->created_at->format('M d, Y') }}</div>
                                                        <small class="text-muted">{{ $task->created_at->format('h:i A') }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('vendor.tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">No tasks assigned to you yet!</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                @if($tasks->hasPages())
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $tasks->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 16px;
    font-weight: bold;
}
</style>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 on filter dropdown
    $('#filter_status').select2({
        theme: 'bootstrap-5',
        placeholder: function() {
            return $(this).data('placeholder');
        },
        allowClear: true,
        width: '100%'
    });
});
</script>
@endsection
