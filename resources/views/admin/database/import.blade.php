@extends('admin.layouts.app')

@section('title', 'Database Import')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Database Import'])
            
            <div class="pt-4 pb-2">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="fas fa-upload text-theme"></i> Database Import
                                </h4>
                                <p class="mb-0 text-muted small mt-1">Import SQL file to restore database tables</p>
                            </div>
                            <a href="{{ route('admin.database.export.index') }}" class="btn btn-theme rounded-pill px-4">
                                <i class="fas fa-download me-2"></i> Export Database
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

                    <!-- Warning Alert -->
                    <div class="alert alert-warning border-0 rounded-3 px-4 py-3 mb-4">
                        <h5 class="alert-heading fw-bold">
                            <i class="fas fa-exclamation-triangle me-2"></i> Warning!
                        </h5>
                        <ul class="mb-0">
                            <li>It is essential to take a database backup before importing!</li>
                            <li>Importing may overwrite existing data.</li>
                            <li>Only import trusted SQL files.</li>
                            <li>Large files may take time to import.</li>
                        </ul>
                    </div>

                    <!-- Upload Form -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-file-upload text-theme me-2"></i> Upload SQL File</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.database.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                                @csrf

                                <div class="mb-3">
                                    <label for="sql_file" class="form-label">
                                        <strong>Select SQL File</strong>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" 
                                           class="form-control @error('sql_file') is-invalid @enderror" 
                                           id="sql_file" 
                                           name="sql_file" 
                                           accept=".sql,.txt"
                                           required>
                                    <small class="form-text text-muted">
                                        Supported formats: .sql, .txt | Maximum size: 50MB
                                    </small>
                                    @error('sql_file')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="confirm_import" required>
                                        <label class="form-check-label text-danger" for="confirm_import">
                                            <strong>I understand that this import may affect existing data and I have taken a backup.</strong>
                                        </label>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-theme btn-lg rounded-pill px-5" id="importBtn">
                                        <i class="fas fa-upload me-2"></i> Import Database
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Previous Exports -->
                    @if(count($exports) > 0)
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-bold"><i class="fas fa-history text-theme me-2"></i> Previous Exports</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>File Name</th>
                                                <th>Size</th>
                                                <th>Date</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($exports as $export)
                                                <tr>
                                                    <td>
                                                        <i class="fas fa-file-code text-theme me-2"></i>
                                                        {{ $export['name'] }}
                                                    </td>
                                                    <td>{{ number_format($export['size'] / 1024, 2) }} KB</td>
                                                    <td>{{ date('d M Y, h:i A', $export['date']) }}</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('admin.database.export.download', $export['name']) }}" 
                                                           class="btn btn-sm btn-success rounded-pill" 
                                                           title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-theme rounded-pill" 
                                                                onclick="useExport('{{ $export['name'] }}')"
                                                                title="Use this file">
                                                            <i class="fas fa-upload"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger rounded-pill" 
                                                                onclick="deleteExport('{{ $export['name'] }}')"
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this export file?</p>
                <p class="text-muted mb-0" id="deleteFileName"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Form submission with loading state
document.getElementById('importForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('sql_file');
    const confirmCheckbox = document.getElementById('confirm_import');
    
    if (!fileInput.files.length) {
        e.preventDefault();
        alert('Please select a SQL file!');
        return false;
    }
    
    if (!confirmCheckbox.checked) {
        e.preventDefault();
        alert('Please check the confirmation checkbox!');
        return false;
    }
    
    // Show loading state
    const importBtn = document.getElementById('importBtn');
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Importing... Please wait...';
    
    // Show progress message
    const progressDiv = document.createElement('div');
    progressDiv.className = 'alert alert-info border-0 rounded-pill px-4 py-3 mt-3';
    progressDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Import in progress... This may take a few minutes for large files.';
    this.appendChild(progressDiv);
});

// Use export file
function useExport(filename) {
    if (confirm('Do you want to import this export file?\n\nFile: ' + filename)) {
        // Download and suggest user to upload it
        window.location.href = "{{ url('admin/database/export/download') }}/" + filename;
        alert('File downloaded! Now upload it.');
    }
}

// Delete export
function deleteExport(filename) {
    document.getElementById('deleteFileName').textContent = filename;
    document.getElementById('deleteForm').action = "{{ url('admin/database/export/delete') }}/" + filename;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endpush

@endsection
