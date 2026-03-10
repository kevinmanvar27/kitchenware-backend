@extends('admin.layouts.app')

@section('title', 'Create Attribute')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Create Attribute'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Create New Attribute</h4>
                                    <p class="mb-0 text-muted">Add a new attribute for variable products</p>
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
                                
                                <form action="{{ route('admin.attributes.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-bold">Attribute Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-pill px-4 py-2" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., Color, Size, Material">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="slug" class="form-label fw-bold">Slug</label>
                                        <input type="text" class="form-control rounded-pill px-4 py-2" id="slug" name="slug" value="{{ old('slug') }}" placeholder="auto-generated-from-name">
                                        <div class="form-text">Leave empty to auto-generate from name</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="description" class="form-label fw-bold">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional description for this attribute">{{ old('description') }}</textarea>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="sort_order" class="form-label fw-bold">Sort Order</label>
                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                        <div class="form-text">Lower numbers appear first</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <h5 class="fw-bold mb-3">Attribute Values</h5>
                                    <p class="text-muted small mb-3">Add values for this attribute (you can add more after creating the attribute)</p>
                                    
                                    <div id="values-container">
                                        <div class="value-row mb-3">
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control rounded-pill px-4" name="values[0][value]" placeholder="Value (e.g., Red, Large)">
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="color" class="form-control form-control-color color-picker" name="values[0][color_code]" value="#000000" title="Choose color (optional)">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-outline-danger rounded-pill w-100 remove-value" disabled>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="button" class="btn btn-outline-primary rounded-pill mb-4" id="add-value">
                                        <i class="fas fa-plus me-2"></i>Add Another Value
                                    </button>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="{{ route('admin.attributes.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Create Attribute
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        const valuesContainer = document.getElementById('values-container');
        const addValueBtn = document.getElementById('add-value');
        let valueIndex = 1;
        
        // Auto-generate slug from name
        nameInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
            }
        });
        
        slugInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
        
        // Add new value row
        addValueBtn.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'value-row mb-3';
            newRow.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" class="form-control rounded-pill px-4" name="values[${valueIndex}][value]" placeholder="Value (e.g., Red, Large)">
                    </div>
                    <div class="col-md-4">
                        <input type="color" class="form-control form-control-color color-picker" name="values[${valueIndex}][color_code]" value="#000000" title="Choose color (optional)">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger rounded-pill w-100 remove-value">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            valuesContainer.appendChild(newRow);
            valueIndex++;
            updateRemoveButtons();
        });
        
        // Remove value row
        valuesContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-value')) {
                const row = e.target.closest('.value-row');
                if (valuesContainer.querySelectorAll('.value-row').length > 1) {
                    row.remove();
                    updateRemoveButtons();
                }
            }
        });
        
        function updateRemoveButtons() {
            const rows = valuesContainer.querySelectorAll('.value-row');
            rows.forEach((row, index) => {
                const removeBtn = row.querySelector('.remove-value');
                removeBtn.disabled = rows.length === 1;
            });
        }
    });
</script>
@endsection
