@extends('vendor.layouts.app')

@section('title', 'Categories')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Category Management'])
            
            @section('page-title', 'Categories')
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Category Management</h4>
                                        <p class="mb-0 text-muted small">Manage your product categories and subcategories</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="showCategoryModal()">
                                        <i class="fas fa-plus me-1 me-md-2"></i><span class="d-none d-sm-inline">Add New Category</span><span class="d-sm-none">Add</span>
                                    </button>
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
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="categoriesTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>SR No.</th>
                                                <th>Category</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($categories as $index => $category)
                                                <tr>
                                                    <td class="fw-bold">{{ $categories->firstItem() + $index }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($category->image && $category->image_url)
                                                                <img src="{{ $category->image_url }}" 
                                                                     class="rounded me-3" width="40" height="40" alt="{{ $category->name }}" 
                                                                     style="object-fit: cover;"
                                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                                     loading="lazy">
                                                                <div class="bg-light rounded me-3 d-none align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            @else
                                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <div class="fw-medium">{{ $category->name }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($category->description)
                                                            <span class="text-muted">{{ Str::limit($category->description, 50) }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($category->is_active)
                                                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                                                Active
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                                                                Inactive
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <?php $categoryId = $category->id; ?>
                                                            <button type="button" class="btn btn-outline-info rounded-start-pill px-3" onclick="showSubCategories(<?php echo $categoryId; ?>)">
                                                                <i class="fas fa-list"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="editCategory(<?php echo $categoryId; ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger rounded-end-pill px-3" onclick="deleteCategory(<?php echo $categoryId; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-tags fa-2x mb-3"></i>
                                                            <p class="mb-0">No categories found</p>
                                                            <p class="small">Try creating a new category</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                @if($categories->hasPages())
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $categories->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    @csrf
                    <input type="hidden" id="categoryId" name="id">
                    <input type="hidden" id="categoryMethod" name="_method">
                    
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-pill" id="categoryName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <div class="border rounded-3 p-3 text-center position-relative" id="category-image-preview">
                            <div class="upload-area" id="category-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                <div>
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                                </div>
                            </div>
                            <input type="file" class="d-none" id="categoryImageInput" name="image" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryStatus" class="form-label">Status</label>
                        <select class="form-select rounded-pill" id="categoryStatus" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-theme rounded-pill" onclick="saveCategory()">
                    <i class="fas fa-save me-2"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Subcategories Modal -->
<div class="modal fade" id="subcategoriesModal" tabindex="-1" aria-labelledby="subcategoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subcategoriesModalLabel">Subcategories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 id="subcategoryParentName">Subcategories</h6>
                    <button type="button" class="btn btn-theme rounded-pill" data-bs-toggle="modal" data-bs-target="#subcategoryModal" onclick="showSubCategoryModal()">
                        <i class="fas fa-plus me-2"></i> Add Subcategory
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>SR No.</th>
                                <th>Subcategory</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="subcategoriesTableBody">
                            <!-- Subcategories will be loaded here -->
                        </tbody>
                    </table>
                </div>
                
                <div id="subcategoriesPagination" class="d-flex justify-content-center mt-3">
                    <!-- Pagination will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Subcategory Modal -->
<div class="modal fade" id="subcategoryModal" tabindex="-1" aria-labelledby="subcategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subcategoryModalLabel">Add New Subcategory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="subcategoryForm">
                    @csrf
                    <input type="hidden" id="subcategoryId" name="id">
                    <input type="hidden" id="subcategoryMethod" name="_method">
                    <input type="hidden" id="subcategoryCategoryId" name="category_id">
                    
                    <div class="mb-3">
                        <label for="subcategoryName" class="form-label">Subcategory Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-pill" id="subcategoryName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subcategoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="subcategoryDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Subcategory Image</label>
                        <div class="border rounded-3 p-3 text-center position-relative" id="subcategory-image-preview">
                            <div class="upload-area" id="subcategory-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                <div>
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                                </div>
                            </div>
                            <input type="file" class="d-none" id="subcategoryImageInput" name="image" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subcategoryStatus" class="form-label">Status</label>
                        <select class="form-select rounded-pill" id="subcategoryStatus" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-theme rounded-pill" onclick="saveSubCategory()">
                    <i class="fas fa-save me-2"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    /* Drag and drop styles for all upload areas */
    #category-image-upload-area.drag-over,
    #subcategory-image-upload-area.drag-over {
        border-color: var(--theme-color, #FF6B00) !important;
        background-color: rgba(255, 107, 0, 0.05);
    }
    
    /* Add more specific styles for better visual feedback */
    #category-image-upload-area,
    #subcategory-image-upload-area {
        transition: all 0.2s ease;
    }
    
    #category-image-upload-area:hover,
    #subcategory-image-upload-area:hover {
        border-color: var(--theme-color, #FF6B00);
        background-color: rgba(255, 107, 0, 0.03);
    }
    
    /* Upload progress indicator */
    .upload-progress-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        z-index: 10;
    }
</style>
@endsection

@section('scripts')
<script>
    // Base URL for AJAX requests
    const baseUrl = '{{ url('/') }}';
    
    let currentCategoryId = null;
    let categoryImageFile = null; // Store the selected file
    let subcategoryImageFile = null; // Store the selected file
    
    $(document).ready(function() {
        // Initialize DataTable with a more robust check and delay
        setTimeout(function() {
            if (typeof $.fn.DataTable !== 'undefined') {
                // Destroy existing DataTable instance if it exists
                if ($.fn.DataTable.isDataTable('#categoriesTable')) {
                    $('#categoriesTable').DataTable().destroy();
                }
                
                // Only initialize if there are categories
                if ($('#categoriesTable tbody tr td[colspan="5"]').length === 0) {
                    $('#categoriesTable').DataTable({
                        "pageLength": 10,
                        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        "ordering": true,
                        "searching": true,
                        "info": true,
                        "paging": false, // Using Laravel pagination
                        "columnDefs": [
                            { "orderable": false, "targets": [4] } // Disable sorting on Actions column
                        ],
                        "language": {
                            "search": "Search:",
                            "info": "Showing _START_ to _END_ of _TOTAL_ categories",
                            "infoEmpty": "Showing 0 to 0 of 0 categories",
                        }
                    });
                }
            }
        }, 100);
        
        // Click handler for category image upload area (triggers file input)
        $(document).on('click', '#category-image-upload-area', function(e) {
            $('#categoryImageInput').click();
        });
        
        // Click handler for subcategory image upload area (triggers file input)
        $(document).on('click', '#subcategory-image-upload-area', function(e) {
            $('#subcategoryImageInput').click();
        });
        
        // Handle category image file selection
        $(document).on('change', '#categoryImageInput', function() {
            if (this.files && this.files[0]) {
                categoryImageFile = this.files[0]; // Store the file
                previewImage(this.files[0], 'category');
            }
        });
        
        // Handle subcategory image file selection
        $(document).on('change', '#subcategoryImageInput', function() {
            if (this.files && this.files[0]) {
                subcategoryImageFile = this.files[0]; // Store the file
                previewImage(this.files[0], 'subcategory');
            }
        });
        
        // Drag and drop for Category Image Upload Area
        $(document).on('dragover', '#category-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragleave', '#category-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        });
        
        $(document).on('drop', '#category-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                categoryImageFile = files[0]; // Store the file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);
                $('#categoryImageInput')[0].files = dataTransfer.files;
                previewImage(files[0], 'category');
            } else {
                alert('Please drop an image file (JPEG, PNG, GIF, or WEBP).');
            }
        });
        
        // Drag and drop for Subcategory Image Upload Area
        $(document).on('dragover', '#subcategory-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragleave', '#subcategory-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        });
        
        $(document).on('drop', '#subcategory-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                subcategoryImageFile = files[0]; // Store the file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);
                $('#subcategoryImageInput')[0].files = dataTransfer.files;
                previewImage(files[0], 'subcategory');
            } else {
                alert('Please drop an image file (JPEG, PNG, GIF, or WEBP).');
            }
        });
    });
    
    // Preview image before upload
    function previewImage(file, target) {
        // Store the file reference in global variables
        if (target === 'category') {
            categoryImageFile = file;
            console.log('Category image stored:', file.name, file.size, 'bytes');
        } else if (target === 'subcategory') {
            subcategoryImageFile = file;
            console.log('Subcategory image stored:', file.name, file.size, 'bytes');
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const imageUrl = e.target.result;
            const previewHtml = `
                <div class="position-relative">
                    <img src="${imageUrl}" class="img-fluid rounded" alt="Preview" style="max-height: 200px; object-fit: contain;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="remove${target.charAt(0).toUpperCase() + target.slice(1)}Image()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <input type="file" class="d-none" id="${target}ImageInput" name="image" accept="image/*">
            `;
            
            if (target === 'category') {
                $('#category-image-preview').html(previewHtml);
                // Re-attach change event handler
                $(document).off('change', '#categoryImageInput').on('change', '#categoryImageInput', function() {
                    if (this.files && this.files[0]) {
                        previewImage(this.files[0], 'category');
                    }
                });
            } else if (target === 'subcategory') {
                $('#subcategory-image-preview').html(previewHtml);
                // Re-attach change event handler
                $(document).off('change', '#subcategoryImageInput').on('change', '#subcategoryImageInput', function() {
                    if (this.files && this.files[0]) {
                        previewImage(this.files[0], 'subcategory');
                    }
                });
            }
        };
        reader.readAsDataURL(file);
    }
    
    // Show category modal for creating new category
    function showCategoryModal() {
        // Clear stored file reference
        categoryImageFile = null;
        console.log('Opening new category modal - file reference cleared');
        
        $('#categoryModalLabel').text('Add New Category');
        $('#categoryForm')[0].reset();
        $('#categoryId').val('');
        $('#categoryMethod').val('');
        $('#category-image-preview').html(`
            <div class="upload-area" id="category-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div>
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                </div>
            </div>
            <input type="file" class="d-none" id="categoryImageInput" name="image" accept="image/*">
        `);
    }
    
    // Edit existing category
    function editCategory(id) {
        // Clear stored file reference when opening edit modal
        categoryImageFile = null;
        console.log('Opening edit category modal - file reference cleared');
        
        $.ajax({
            url: baseUrl + '/vendor/categories/' + id,
            type: 'GET',
            success: function(data) {
                console.log('Category data loaded:', data);
                $('#categoryModalLabel').text('Edit Category');
                $('#categoryId').val(data.id);
                $('#categoryMethod').val('PUT');
                $('#categoryName').val(data.name);
                $('#categoryDescription').val(data.description);
                $('#categoryStatus').val(data.is_active ? '1' : '0');
                
                if (data.image_url && data.image_url !== null) {
                    console.log('Category has image:', data.image_url);
                    $('#category-image-preview').html(`
                        <div class="position-relative">
                            <img src="${data.image_url}" class="img-fluid rounded" alt="${data.name}" style="max-height: 200px; object-fit: contain;" 
                                 onerror="console.error('Failed to load image:', this.src); this.parentElement.innerHTML='<div class=\\'alert alert-warning\\'>Image file not found</div>';">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeCategoryImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <input type="file" class="d-none" id="categoryImageInput" name="image" accept="image/*">
                    `);
                } else {
                    console.log('Category has no image');
                    $('#category-image-preview').html(`
                        <div class="upload-area" id="category-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <div>
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                            </div>
                        </div>
                        <input type="file" class="d-none" id="categoryImageInput" name="image" accept="image/*">
                    `);
                }
                
                $('#categoryModal').modal('show');
            },
            error: function(xhr) {
                console.error('Error loading category:', xhr);
                alert('Error loading category data.');
            }
        });
    }
    
    // Save category (create or update)
    function saveCategory() {
        const id = $('#categoryId').val();
        const url = id ? baseUrl + '/vendor/categories/' + id : baseUrl + '/vendor/categories';
        
        // Build FormData manually
        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('name', $('#categoryName').val());
        formData.append('description', $('#categoryDescription').val());
        formData.append('is_active', $('#categoryStatus').val());
        
        if (id) {
            formData.append('_method', 'PUT');
        }
        
        // Add image file if selected (use stored file)
        if (categoryImageFile) {
            formData.append('image', categoryImageFile);
            console.log('Image file attached:', categoryImageFile.name, 'Size:', categoryImageFile.size, 'bytes');
        } else {
            console.log('No image file selected');
        }
        
        // Log form data for debugging
        console.log('Submitting category form to:', url);
        for (let pair of formData.entries()) {
            if (pair[1] instanceof File) {
                console.log(pair[0] + ': [File] ' + pair[1].name);
            } else {
                console.log(pair[0] + ': ' + pair[1]);
            }
        }
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Category saved successfully:', response);
                if (response.success) {
                    categoryImageFile = null; // Reset the stored file
                    $('#categoryModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                console.error('Error saving category:', xhr);
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    for (let field in errors) {
                        errorMessages += errors[field].join(', ') + '\n';
                    }
                    alert('Validation errors:\n' + errorMessages);
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    alert('Error: ' + xhr.responseJSON.error);
                } else {
                    alert('Error saving category. Please check the console for details.');
                }
            }
        });
    }
    
    // Delete category
    function deleteCategory(id) {
        if (confirm('Are you sure you want to delete this category? This will also delete all subcategories.')) {
            $.ajax({
                url: baseUrl + '/vendor/categories/' + id,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    alert('Error deleting category.');
                }
            });
        }
    }
    
    // Remove category image
    function removeCategoryImage() {
        // Clear the stored file reference
        categoryImageFile = null;
        console.log('Category image cleared');
        
        $('#categoryImageInput').val('');
        $('#category-image-preview').html(`
            <div class="upload-area" id="category-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div>
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                </div>
            </div>
            <input type="file" class="d-none" id="categoryImageInput" name="image" accept="image/*">
        `);
    }
    
    // Show subcategories for a category
    function showSubCategories(categoryId) {
        currentCategoryId = categoryId;
        loadSubCategories(categoryId, 1);
        $('#subcategoriesModal').modal('show');
    }
    
    // Load subcategories
    function loadSubCategories(categoryId, page = 1) {
        $.ajax({
            url: baseUrl + '/vendor/categories/' + categoryId + '/subcategories?page=' + page,
            type: 'GET',
            success: function(data) {
                // Set parent category name
                if (data.data && data.data.length > 0 && data.data[0].category) {
                    $('#subcategoryParentName').text('Subcategories for ' + data.data[0].category.name);
                } else {
                    $('#subcategoryParentName').text('Subcategories');
                }
                
                // Populate table
                let html = '';
                if (data.data && data.data.length > 0) {
                    data.data.forEach(function(subcategory, index) {
                        html += `
                            <tr>
                                <td>${data.from + index}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        ${subcategory.image && subcategory.image_url ? 
                                            `<img src="${subcategory.image_url}" class="rounded me-3" width="40" height="40" alt="${subcategory.name}" style="object-fit: cover;" 
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" loading="lazy">
                                             <div class="bg-light rounded me-3 d-none align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-image text-muted"></i>
                                             </div>` :
                                            `<div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>`
                                        }
                                        <div>
                                            <div class="fw-medium">${subcategory.name}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>${subcategory.description ? subcategory.description.substring(0, 50) + (subcategory.description.length > 50 ? '...' : '') : 'N/A'}</td>
                                <td>
                                    ${subcategory.is_active ? 
                                        `<span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">Active</span>` :
                                        `<span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">Inactive</span>`
                                    }
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary rounded-start-pill px-3" data-bs-toggle="modal" data-bs-target="#subcategoryModal" onclick="editSubCategory(${subcategory.id})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger rounded-end-pill px-3" onclick="deleteSubCategory(${subcategory.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = `
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-list fa-2x mb-3"></i>
                                    <p class="mb-0">No subcategories found</p>
                                    <p class="small">Try creating a new subcategory</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
                
                $('#subcategoriesTableBody').html(html);
                
                // Populate pagination
                if (data.last_page > 1) {
                    let paginationHtml = `
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                ${data.prev_page_url ? 
                                    `<li class="page-item"><a class="page-link rounded-pill" href="javascript:void(0)" onclick="loadSubCategories(${categoryId}, ${data.current_page - 1})">Previous</a></li>` :
                                    `<li class="page-item disabled"><span class="page-link rounded-pill">Previous</span></li>`
                                }
                    `;
                    
                    for (let i = 1; i <= data.last_page; i++) {
                        paginationHtml += `
                            <li class="page-item ${i === data.current_page ? 'active' : ''}">
                                <a class="page-link rounded-pill" href="javascript:void(0)" onclick="loadSubCategories(${categoryId}, ${i})">${i}</a>
                            </li>
                        `;
                    }
                    
                    paginationHtml += `
                                ${data.next_page_url ? 
                                    `<li class="page-item"><a class="page-link rounded-pill" href="javascript:void(0)" onclick="loadSubCategories(${categoryId}, ${data.current_page + 1})">Next</a></li>` :
                                    `<li class="page-item disabled"><span class="page-link rounded-pill">Next</span></li>`
                                }
                            </ul>
                        </nav>
                    `;
                    
                    $('#subcategoriesPagination').html(paginationHtml);
                } else {
                    $('#subcategoriesPagination').html('');
                }
            },
            error: function() {
                alert('Error loading subcategories.');
            }
        });
    }
    
    // Show subcategory modal for creating new subcategory
    function showSubCategoryModal() {
        // Clear stored file reference
        subcategoryImageFile = null;
        console.log('Opening new subcategory modal - file reference cleared');
        
        $('#subcategoryModalLabel').text('Add New Subcategory');
        $('#subcategoryForm')[0].reset();
        $('#subcategoryId').val('');
        $('#subcategoryMethod').val('');
        $('#subcategoryCategoryId').val(currentCategoryId);
        $('#subcategory-image-preview').html(`
            <div class="upload-area" id="subcategory-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div>
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                </div>
            </div>
            <input type="file" class="d-none" id="subcategoryImageInput" name="image" accept="image/*">
        `);
    }
    
    // Edit existing subcategory
    function editSubCategory(id) {
        // Clear stored file reference when opening edit modal
        subcategoryImageFile = null;
        console.log('Opening edit subcategory modal - file reference cleared');
        
        $.ajax({
            url: baseUrl + '/vendor/subcategories/' + id,
            type: 'GET',
            success: function(data) {
                $('#subcategoryModalLabel').text('Edit Subcategory');
                $('#subcategoryId').val(data.id);
                $('#subcategoryMethod').val('PUT');
                $('#subcategoryCategoryId').val(data.category_id);
                $('#subcategoryName').val(data.name);
                $('#subcategoryDescription').val(data.description);
                $('#subcategoryStatus').val(data.is_active ? '1' : '0');
                
                if (data.image_url && data.image_url !== null) {
                    $('#subcategory-image-preview').html(`
                        <div class="position-relative">
                            <img src="${data.image_url}" class="img-fluid rounded" alt="${data.name}" style="max-height: 200px; object-fit: contain;"
                                 onerror="this.parentElement.innerHTML='<div class=\\'alert alert-warning\\'>Image file not found</div>';">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeSubcategoryImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <input type="file" class="d-none" id="subcategoryImageInput" name="image" accept="image/*">
                    `);
                } else {
                    $('#subcategory-image-preview').html(`
                        <div class="upload-area" id="subcategory-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <div>
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                            </div>
                        </div>
                        <input type="file" class="d-none" id="subcategoryImageInput" name="image" accept="image/*">
                    `);
                }
                
                $('#subcategoryModal').modal('show');
            },
            error: function() {
                alert('Error loading subcategory data.');
            }
        });
    }
    
    // Save subcategory (create or update)
    function saveSubCategory() {
        const id = $('#subcategoryId').val();
        const url = id ? baseUrl + '/vendor/subcategories/' + id : baseUrl + '/vendor/subcategories';
        
        // Build FormData manually
        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('category_id', currentCategoryId);
        formData.append('name', $('#subcategoryName').val());
        formData.append('description', $('#subcategoryDescription').val());
        formData.append('is_active', $('#subcategoryStatus').val());
        
        if (id) {
            formData.append('_method', 'PUT');
        }
        
        // Use stored file reference instead of reading from input
        if (subcategoryImageFile) {
            console.log('Adding subcategory image to FormData:', subcategoryImageFile);
            formData.append('image', subcategoryImageFile);
        } else {
            console.log('No subcategory image file stored');
        }
        
        // Debug: Log all FormData entries
        console.log('FormData contents:');
        for (let pair of formData.entries()) {
            console.log(pair[0], pair[1]);
        }
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#subcategoryModal').modal('hide');
                    loadSubCategories(currentCategoryId, 1);
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    for (let field in errors) {
                        errorMessages += errors[field].join(', ') + '\n';
                    }
                    alert('Validation errors:\n' + errorMessages);
                } else {
                    alert('Error saving subcategory.');
                }
            }
        });
    }
    
    // Delete subcategory
    function deleteSubCategory(id) {
        if (confirm('Are you sure you want to delete this subcategory?')) {
            $.ajax({
                url: baseUrl + '/vendor/subcategories/' + id,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        loadSubCategories(currentCategoryId, 1);
                    }
                },
                error: function() {
                    alert('Error deleting subcategory.');
                }
            });
        }
    }
    
    // Remove subcategory image
    function removeSubcategoryImage() {
        // Clear the stored file reference
        subcategoryImageFile = null;
        console.log('Subcategory image cleared');
        
        $('#subcategoryImageInput').val('');
        $('#subcategory-image-preview').html(`
            <div class="upload-area" id="subcategory-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div>
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                </div>
            </div>
            <input type="file" class="d-none" id="subcategoryImageInput" name="image" accept="image/*">
        `);
    }
</script>
@endsection
