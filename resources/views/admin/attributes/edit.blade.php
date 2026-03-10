@extends('admin.layouts.app')

@section('title', 'Edit Attribute')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Edit Attribute'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Edit Attribute: {{ $attribute->name }}</h4>
                                    <p class="mb-0 text-muted">Update attribute details and values</p>
                                </div>
                                <a href="{{ route('admin.attributes.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </a>
                            </div>
                            
                            <div class="card-body">
                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <form action="{{ route('admin.attributes.update', $attribute) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-bold">Attribute Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-pill px-4 py-2" id="name" name="name" value="{{ old('name', $attribute->name) }}" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="slug" class="form-label fw-bold">Slug</label>
                                        <input type="text" class="form-control rounded-pill px-4 py-2" id="slug" name="slug" value="{{ old('slug', $attribute->slug) }}">
                                        <div class="form-text">Leave empty to auto-generate from name</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="description" class="form-label fw-bold">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $attribute->description) }}</textarea>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="sort_order" class="form-label fw-bold">Sort Order</label>
                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="sort_order" name="sort_order" value="{{ old('sort_order', $attribute->sort_order) }}" min="0">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $attribute->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="{{ route('admin.attributes.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Update Attribute
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Attribute Values Section -->
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title mb-0 fw-bold">Attribute Values</h5>
                                        <p class="mb-0 text-muted small">Manage values for this attribute</p>
                                    </div>
                                    <button type="button" class="btn btn-theme rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addValueModal">
                                        <i class="fas fa-plus me-2"></i>Add Value
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Value</th>
                                                <th>Slug</th>
                                                <th>Color</th>
                                                <th>Sort Order</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($attribute->values as $value)
                                                <tr>
                                                    <td>{{ $value->value }}</td>
                                                    <td><code>{{ $value->slug }}</code></td>
                                                    <td>
                                                        @if($value->color_code)
                                                            <span class="d-inline-block rounded" style="width: 24px; height: 24px; background-color: {{ $value->color_code }}; border: 1px solid #ddd;"></span>
                                                            <code class="ms-2">{{ $value->color_code }}</code>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $value->sort_order }}</td>
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 edit-value-btn" 
                                                            data-value-id="{{ $value->id }}"
                                                            data-value="{{ $value->value }}"
                                                            data-slug="{{ $value->slug }}"
                                                            data-color-code="{{ $value->color_code }}"
                                                            data-sort-order="{{ $value->sort_order }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('admin.attributes.values.destroy', [$attribute, $value]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this value?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        No values defined for this attribute.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<!-- Add Value Modal -->
<div class="modal fade" id="addValueModal" tabindex="-1" aria-labelledby="addValueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.attributes.values.store', $attribute) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addValueModalLabel">Add Attribute Value</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_value" class="form-label fw-bold">Value <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-pill px-4" id="add_value" name="value" required placeholder="e.g., Red, Large, Cotton">
                    </div>
                    <div class="mb-3">
                        <label for="add_slug" class="form-label fw-bold">Slug</label>
                        <input type="text" class="form-control rounded-pill px-4" id="add_slug" name="slug" placeholder="auto-generated">
                        <div class="form-text">Leave empty to auto-generate</div>
                    </div>
                    <div class="mb-3">
                        <label for="add_color_code" class="form-label fw-bold">Color Code (Optional)</label>
                        <input type="color" class="form-control form-control-color" id="add_color_code" name="color_code" value="#000000">
                        <div class="form-text">Only for color-type attributes</div>
                    </div>
                    <div class="mb-3">
                        <label for="add_sort_order" class="form-label fw-bold">Sort Order</label>
                        <input type="number" class="form-control rounded-pill px-4" id="add_sort_order" name="sort_order" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme rounded-pill px-4">Add Value</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Value Modal -->
<div class="modal fade" id="editValueModal" tabindex="-1" aria-labelledby="editValueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editValueForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editValueModalLabel">Edit Attribute Value</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_value" class="form-label fw-bold">Value <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-pill px-4" id="edit_value" name="value" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_slug" class="form-label fw-bold">Slug</label>
                        <input type="text" class="form-control rounded-pill px-4" id="edit_slug" name="slug">
                    </div>
                    <div class="mb-3">
                        <label for="edit_color_code" class="form-label fw-bold">Color Code</label>
                        <input type="color" class="form-control form-control-color" id="edit_color_code" name="color_code">
                    </div>
                    <div class="mb-3">
                        <label for="edit_sort_order" class="form-label fw-bold">Sort Order</label>
                        <input type="number" class="form-control rounded-pill px-4" id="edit_sort_order" name="sort_order" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme rounded-pill px-4">Update Value</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        
        // Auto-generate slug from name
        nameInput.addEventListener('input', function() {
            if (!slugInput.dataset.manual) {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
                slugInput.value = slug;
            }
        });
        
        slugInput.addEventListener('input', function() {
            this.dataset.manual = 'true';
        });
        
        // Auto-generate slug for add value modal
        const addValueInput = document.getElementById('add_value');
        const addSlugInput = document.getElementById('add_slug');
        
        addValueInput.addEventListener('input', function() {
            if (!addSlugInput.value || addSlugInput.dataset.autoGenerated === 'true') {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
                addSlugInput.value = slug;
                addSlugInput.dataset.autoGenerated = 'true';
            }
        });
        
        addSlugInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
        
        // Edit value modal
        const editValueModal = document.getElementById('editValueModal');
        const editValueForm = document.getElementById('editValueForm');
        const editValueBtns = document.querySelectorAll('.edit-value-btn');
        
        editValueBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const valueId = this.dataset.valueId;
                const value = this.dataset.value;
                const slug = this.dataset.slug;
                const colorCode = this.dataset.colorCode;
                const sortOrder = this.dataset.sortOrder;
                
                // Set form action
                editValueForm.action = '{{ url("admin/attributes/" . $attribute->id . "/values") }}/' + valueId;
                
                // Fill form fields
                document.getElementById('edit_value').value = value;
                document.getElementById('edit_slug').value = slug;
                document.getElementById('edit_color_code').value = colorCode || '#000000';
                document.getElementById('edit_sort_order').value = sortOrder;
                
                // Show modal
                const modal = new bootstrap.Modal(editValueModal);
                modal.show();
            });
        });
    });
</script>
@endsection
