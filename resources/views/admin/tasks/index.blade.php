@extends('admin.layouts.app')

@section('title', 'Task Management')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Task Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold">{{ $statistics['total'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">Total Tasks</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm border-start border-warning border-3">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold text-warning">{{ $statistics['pending'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">Pending</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm border-start border-info border-3">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold text-info">{{ $statistics['in_progress'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">In Progress</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm border-start border-danger border-3">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold text-danger">{{ $statistics['with_questions'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">Questions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm border-start border-primary border-3">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold text-primary">{{ $statistics['done'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">Done</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card border-0 shadow-sm border-start border-success border-3">
                            <div class="card-body text-center">
                                <h3 class="mb-0 fw-bold text-success">{{ $statistics['verified'] ?? 0 }}</h3>
                                <p class="mb-0 text-muted small">Verified</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">All Tasks</h4>
                                    <p class="mb-0 text-muted">Manage and assign tasks to vendors</p>
                                </div>
                                <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-plus me-2"></i>Create New Task
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

                                <!-- Filters -->
                                <form method="GET" action="{{ route('admin.tasks.index') }}" class="mb-4">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <input type="text" name="search" class="form-control rounded-pill" placeholder="Search tasks..." value="{{ request('search') }}">
                                        </div>
                                        <div class="col-md-2">
                                            <select name="status" id="filter_status" class="form-select rounded-pill select2" data-placeholder="All Status">
                                                <option value="">All Status</option>
                                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                <option value="question" {{ request('status') == 'question' ? 'selected' : '' }}>Question</option>
                                                <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="vendor_id" id="filter_vendor" class="form-select rounded-pill select2" data-placeholder="All Vendors">
                                                <option value="">All Vendors</option>
                                                @foreach($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                                        {{ $vendor->store_name ?? $vendor->user->name ?? 'Unknown Vendor' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary rounded-pill w-100">
                                                <i class="fas fa-filter me-2"></i>Filter
                                            </button>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary rounded-pill w-100">
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
                                                <th>Assigned To</th>
                                                <th>Vendor</th>
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
                                                        @if($task->assignedTo)
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                                    {{ substr($task->assignedTo->name, 0, 1) }}
                                                                </div>
                                                                <div>
                                                                    <div>{{ $task->assignedTo->name }}</div>
                                                                    <small class="text-muted">{{ $task->assignedTo->email }}</small>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">Not assigned</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($task->vendor)
                                                            {{ $task->vendor->name }}
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
                                                        <span class="badge bg-{{ $color }} rounded-pill">
                                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>{{ $task->created_at->format('M d, Y') }}</small><br>
                                                        <small class="text-muted">{{ $task->created_at->format('h:i A') }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('admin.tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('admin.tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('admin.tasks.destroy', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-5">
                                                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">No tasks found. Create your first task!</p>
                                                        <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary rounded-pill">
                                                            <i class="fas fa-plus me-2"></i>Create Task
                                                        </a>
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
    // Initialize Select2 on filter dropdowns
    $('#filter_status, #filter_vendor').select2({
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
