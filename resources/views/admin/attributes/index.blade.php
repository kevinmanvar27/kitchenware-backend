@extends('admin.layouts.app')

@section('title', 'Product Attributes')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Product Attributes'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Product Attributes</h4>
                                        <p class="mb-0 text-muted small">Manage attributes for variable products</p>
                                    </div>
                                    <a href="{{ route('admin.attributes.create') }}" class="btn btn-theme rounded-pill px-4">
                                        <i class="fas fa-plus me-2"></i>Add Attribute
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
                                
                                <div class="row">
                                    @forelse($attributes as $attribute)
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100 border">
                                                <div class="card-header bg-light">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0 fw-bold">
                                                            <i class="fas fa-tag me-2 text-primary"></i>{{ $attribute->name }}
                                                        </h6>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-info">{{ $attribute->values->count() }} values</span>
                                                            @if(!$attribute->is_active)
                                                                <span class="badge bg-secondary">Inactive</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($attribute->vendor)
                                                        <div class="mt-2">
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-store me-1"></i>{{ $attribute->vendor->store_name }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        <div class="mt-2">
                                                            <span class="badge bg-warning text-dark">
                                                                <i class="fas fa-globe me-1"></i>Global
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted small mb-2">
                                                        <strong>Slug:</strong> <code>{{ $attribute->slug }}</code>
                                                    </p>
                                                    @if($attribute->description)
                                                        <p class="text-muted small mb-2">{{ Str::limit($attribute->description, 80) }}</p>
                                                    @endif
                                                    <div class="d-flex flex-wrap gap-1 mb-3">
                                                        @forelse($attribute->values as $value)
                                                            @if($value->color_code)
                                                                <span class="badge border" style="background-color: {{ $value->color_code }}; color: {{ $value->color_code > '#888888' ? '#000' : '#fff' }}">
                                                                    {{ $value->value }}
                                                                </span>
                                                            @else
                                                                <span class="badge bg-secondary">{{ $value->value }}</span>
                                                            @endif
                                                        @empty
                                                            <span class="text-muted small">No values defined</span>
                                                        @endforelse
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-white border-top">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.attributes.destroy', $attribute) }}" method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 delete-btn" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="text-center py-5">
                                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">No product attributes available.</p>
                                                <p class="text-muted small">Click "Add Attribute" to create your first attribute.</p>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                                
                                @if($attributes->hasPages())
                                    <div class="mt-4">
                                        {{ $attributes->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete this attribute? This action cannot be undone and will also delete all its values.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let formToSubmit = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    // Handle delete button clicks
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            formToSubmit = this.closest('form');
            deleteModal.show();
        });
    });
    
    // Handle confirm delete
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (formToSubmit) {
            formToSubmit.submit();
        }
    });
});
</script>
@endsection
