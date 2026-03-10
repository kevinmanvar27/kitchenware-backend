@extends('admin.layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Edit Task'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Edit Task</h4>
                                    <p class="mb-0 text-muted">Update task details and assignment</p>
                                </div>
                                <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Tasks
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
                                
                                <form action="{{ route('admin.tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data" id="task-form">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-4">
                                                <label for="title" class="form-label fw-bold">Task Title <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill @error('title') is-invalid @enderror" 
                                                       id="title" name="title" value="{{ old('title', $task->title) }}" 
                                                       placeholder="Enter task title" required>
                                                @error('title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="description" class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                                          id="description" name="description" rows="6" 
                                                          placeholder="Enter detailed task description" required>{{ old('description', $task->description) }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-4">
                                                <label for="assigned_to" class="form-label fw-bold">Assign To <span class="text-danger">*</span></label>
                                                <select class="form-select select2-user @error('assigned_to') is-invalid @enderror" 
                                                        id="assigned_to" name="assigned_to" required>
                                                    <option value="">Select a person</option>
                                                    @foreach($vendorStaff as $staff)
                                                        <option value="{{ $staff->id }}" {{ old('assigned_to', $task->assigned_to) == $staff->id ? 'selected' : '' }}>
                                                            {{ $staff->name }} ({{ $staff->email }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('assigned_to')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-4">
                                                <label for="vendor_id" class="form-label fw-bold">Vendor</label>
                                                <select class="form-select select2-vendor @error('vendor_id') is-invalid @enderror" 
                                                        id="vendor_id" name="vendor_id">
                                                    <option value="">Select a vendor (optional)</option>
                                                    @foreach($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}" {{ old('vendor_id', $task->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                                            {{ $vendor->store_name ?? $vendor->user->name ?? 'Vendor #' . $vendor->id }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('vendor_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-4">
                                                <label for="attachment" class="form-label fw-bold">Attachment</label>
                                                @if($task->attachment)
                                                    <div class="mb-2">
                                                        <small class="text-muted">Current file: </small>
                                                        <a href="{{ asset('uploads/tasks/' . $task->attachment) }}" target="_blank" class="text-primary">
                                                            <i class="fas fa-file me-1"></i>{{ $task->attachment }}
                                                        </a>
                                                    </div>
                                                @endif
                                                <input type="file" class="form-control @error('attachment') is-invalid @enderror" 
                                                       id="attachment" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                                                @error('attachment')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">Upload new file to replace existing (PDF, DOC, XLS, Images - Max 10MB)</small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h5 class="card-title fw-bold mb-3">
                                                        <i class="fas fa-info-circle text-primary me-2"></i>Task Status
                                                    </h5>
                                                    <div class="mb-3">
                                                        <strong>Current Status:</strong><br>
                                                        @php
                                                            $statusColors = [
                                                                'pending' => 'warning',
                                                                'in_progress' => 'info',
                                                                'question' => 'danger',
                                                                'done' => 'primary',
                                                                'verified' => 'success'
                                                            ];
                                                            $statusColor = $statusColors[$task->status] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $statusColor }} rounded-pill mt-1">
                                                            {{ ucwords(str_replace('_', ' ', $task->status)) }}
                                                        </span>
                                                    </div>

                                                    <div class="mb-3">
                                                        <strong>Created:</strong><br>
                                                        <small class="text-muted">{{ $task->created_at->format('M d, Y h:i A') }}</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <strong>Last Updated:</strong><br>
                                                        <small class="text-muted">{{ $task->updated_at->format('M d, Y h:i A') }}</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <strong>Assigned By:</strong><br>
                                                        <small class="text-muted">{{ $task->assignedBy->name ?? 'N/A' }}</small>
                                                    </div>

                                                    <hr class="my-3">

                                                    <div class="alert alert-info small mb-0">
                                                        <i class="fas fa-bell me-2"></i>
                                                        If you change the assigned person, they will receive a notification.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                                    <i class="fas fa-times me-2"></i>Cancel
                                                </a>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                                    <i class="fas fa-save me-2"></i>Update Task
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.gap-2 {
    gap: 0.5rem !important;
}
/* Select2 custom styling */
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
.select2-container {
    width: 100% !important;
}
</style>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('.select2-user').select2({
        placeholder: 'Select a person',
        allowClear: true,
        width: '100%'
    });

    $('.select2-vendor').select2({
        placeholder: 'Select a vendor (optional)',
        allowClear: true,
        width: '100%'
    });

    // Form validation
    const form = document.getElementById('task-form');
    
    form.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const assignedTo = document.getElementById('assigned_to').value;
        
        if (!title || !description || !assignedTo) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return false;
        }
    });

    // File upload validation
    const fileInput = document.getElementById('attachment');
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('File size must be less than 10MB');
                this.value = '';
            }
        }
    });
});
</script>
@endsection
