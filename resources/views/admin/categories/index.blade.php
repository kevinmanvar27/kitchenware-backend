@extends('admin.layouts.app')

@section('title', 'Categories')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Category Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Category Management</h4>
                                        <p class="mb-0 text-muted small">Manage product categories and subcategories</p>
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
                                                    <td class="fw-bold">{{ $loop->iteration }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($category->image)
                                                                <img src="{{ $category->image_url }}" 
                                                                     class="rounded me-3" width="40" height="40" alt="{{ $category->name }}" 
                                                                     onerror="this.onerror=null;this.src='{{ asset('images/placeholder.png') }}';"
                                                                     loading="lazy">
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
                                                        @if($category->description && $category->description !== 'N/A')
                                                            <span class="text-muted">{{ Str::limit($category->description, 50) }}</span>
                                                        @else
                                                            <span class="text-muted">—</span>
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
                                                            <button type="button" class="btn btn-outline-info rounded-start-pill px-3" onclick="showSubCategories(<?php echo $categoryId; ?>)" title="View Subcategories" data-bs-toggle="tooltip">
                                                                <i class="fas fa-list"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="editCategory(<?php echo $categoryId; ?>)" title="Edit Category">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger rounded-end-pill px-3" onclick="deleteCategory(<?php echo $categoryId; ?>)" title="Delete Category" data-bs-toggle="tooltip">
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
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
                        <input type="file" class="form-control" id="categoryImage" name="image" accept="image/*" onchange="previewCategoryImage(this)">
                        <div class="mt-2" id="category-image-preview" style="display: none;">
                            <img id="category-preview-img" src="" alt="Category preview" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
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

{{-- MEDIA LIBRARY REMOVED - Modal commented out to prevent errors --}}
{{-- Direct file uploads are now handled inline in forms --}}
{{--
<div class="modal fade" id="mediaLibraryModal" tabindex="-1" aria-labelledby="mediaLibraryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaLibraryModalLabel">Media Library</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 mb-3">
                            <h6 class="mb-3">Upload New Media</h6>
                            <form id="mediaUploadForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="mediaFile" class="form-label">Select File</label>
                                    <div class="upload-area" id="media-upload-area" style="min-height: 100px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items-center; justify-content: center; cursor: pointer;">
                                        <div class="text-center" id="media-upload-content">
                                            <i class="fas fa-cloud-upload-alt text-muted mb-2" style="font-size: 24px;"></i>
                                            <p class="text-muted mb-0 small">Drag & drop file here or click to upload</p>
                                        </div>
                                    </div>
                                    <input type="file" class="form-control d-none" id="mediaFile" name="file" accept="image/*,video/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/csv">
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Existing Media</h6>
                            <div class="d-flex">
                                <input type="text" class="form-control rounded-pill me-2" id="mediaSearch" placeholder="Search media...">
                            </div>
                        </div>
                        
                        <div id="mediaLibraryContent" class="row">
                            <!-- Media items will be loaded here -->
                        </div>
                        
                        <div id="mediaLibraryPagination" class="d-flex justify-content-center mt-3">
                            <!-- Pagination will be loaded here -->
                        </div>
                    </div>
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
--}}

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
                        <input type="file" class="form-control" id="subcategoryImage" name="image" accept="image/*" onchange="previewSubcategoryImage(this)">
                        <div class="mt-2" id="subcategory-image-preview" style="display: none;">
                            <img id="subcategory-preview-img" src="" alt="Subcategory preview" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
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
    #media-upload-area.drag-over,
    #category-image-upload-area.drag-over,
    #subcategory-image-upload-area.drag-over {
        border-color: var(--theme-color, #FF6B00) !important;
        background-color: rgba(255, 107, 0, 0.05);
    }
    
    /* Add more specific styles for better visual feedback */
    #media-upload-area,
    #category-image-upload-area,
    #subcategory-image-upload-area {
        transition: all 0.2s ease;
    }
    
    #media-upload-area:hover,
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
    let currentMediaTarget = null;
    let currentCategoryId = null;
    let currentCategoryName = null;
    
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title], [data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize DataTable with a more robust check and delay
        setTimeout(function() {
            if (typeof $.fn.DataTable !== 'undefined') {
                // Destroy existing DataTable instance if it exists
                if ($.fn.DataTable.isDataTable('#categoriesTable')) {
                    $('#categoriesTable').DataTable().destroy();
                }
                
                // Only clear the table if it has the placeholder row for empty data
                if ($('#categoriesTable tbody tr td[colspan="5"]').length > 0) {
                    $('#categoriesTable tbody').empty();
                }
                
                $('#categoriesTable').DataTable({
                    "pageLength": 10,
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "ordering": true,
                    "searching": true,
                    "info": true,
                    "paging": true,
                    "columnDefs": [
                        { "orderable": false, "targets": [4] } // Disable sorting on Actions column
                    ],
                    "language": {
                        "search": "Search:",
                        "lengthMenu": "Show _MENU_ entries per page",
                        "info": "Showing _START_ to _END_ of _TOTAL_ categories",
                        "infoEmpty": "Showing 0 to 0 of 0 categories",
                        "infoFiltered": "(filtered from _MAX_ total categories)",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        }
                    },
                    "aoColumns": [
                        null, // ID
                        null, // Category
                        null, // Description
                        null, // Status
                        null  // Actions
                    ],
                    "preDrawCallback": function(settings) {
                        // Ensure consistent column count
                        if ($('#categoriesTable tbody tr').length === 0 || ($('#categoriesTable tbody tr').length === 1 && $('#categoriesTable tbody tr td').attr('colspan') !== '5')) {
                            $('#categoriesTable tbody').html('<tr><td colspan="5" class="text-center py-5"><div class="text-muted"><i class="fas fa-tags fa-2x mb-3"></i><p class="mb-0">No categories found</p><p class="small">Try creating a new category</p></div></td></tr>');
                        }
                    },
                    "drawCallback": function(settings) {
                        // Reinitialize tooltips after each draw
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }
                });
                // Adjust select width after DataTable initializes
                $('.dataTables_length select').css('width', '80px');
            } else {
                console.error('DataTables is not available. Please check if the library is properly loaded.');
            }
        }, 100); // Small delay to ensure all scripts are loaded
        // Adjust select width after DataTable initializes
        $('.dataTables_length select').css('width', '80px');
        
        // Media search with debounce
        let mediaSearchTimeout;
        $('#mediaSearch').on('input', function() {
            clearTimeout(mediaSearchTimeout);
            const searchTerm = $(this).val();
            
            mediaSearchTimeout = setTimeout(function() {
                loadMedia(1); // Reset to first page when searching
            }, 300); // 300ms debounce
        });
        
        // Use event delegation for media upload area click handler
        $(document).on('click', '#media-upload-area', function() {
            $('#mediaFile').click();
        });
        
        // Add change handler for file input to auto-upload
        $('#mediaFile').on('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                displayFileInfo(file);
                // Auto-upload the file immediately
                uploadMedia();
            } else {
                // Hide file info when no file is selected
                $('.file-info-container').remove();
            }
        });
        
        // Add drag and drop functionality for media upload area using event delegation
        $(document).on('dragover', '#media-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragenter', '#media-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragleave', '#media-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // Only remove drag-over class if we're actually leaving the element
            if (e.target === this || $(e.target).closest('#media-upload-area').length === 0) {
                $(this).removeClass('drag-over');
            }
        });
        
        $(document).on('drop', '#media-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                // Set the file input value
                $('#mediaFile')[0].files = files;
                
                // Display file information
                const file = files[0];
                displayFileInfo(file);
                
                // Auto-upload the file
                uploadMedia();
            } else {
                // Hide file info when no file is dropped
                $('.file-info-container').remove();
            }
        });
        
        // ============================================
        // Drag and drop for Category Image Upload Area
        // ============================================
        $(document).on('dragover', '#category-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragenter', '#category-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragleave', '#category-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (e.target === this || $(e.target).closest('#category-image-upload-area').length === 0) {
                $(this).removeClass('drag-over');
            }
        });
        
        $(document).on('drop', '#category-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                // Validate it's an image
                if (file.type.startsWith('image/')) {
                    uploadAndSelectImage(file, 'category');
                } else {
                    alert('Please drop an image file (JPEG, PNG, GIF, or WEBP).');
                }
            }
        });
        
        // ================================================
        // Drag and drop for Subcategory Image Upload Area
        // ================================================
        $(document).on('dragover', '#subcategory-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragenter', '#subcategory-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragleave', '#subcategory-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (e.target === this || $(e.target).closest('#subcategory-image-upload-area').length === 0) {
                $(this).removeClass('drag-over');
            }
        });
        
        $(document).on('drop', '#subcategory-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                // Validate it's an image
                if (file.type.startsWith('image/')) {
                    uploadAndSelectImage(file, 'subcategory');
                } else {
                    alert('Please drop an image file (JPEG, PNG, GIF, or WEBP).');
                }
            }
        });
        
        // Initialize media library
        loadMedia();
    });
    
    // Function to display file information
    function displayFileInfo(file) {
        // Create a container for file information
        let fileInfoHtml = `
            <div class="file-info-container mt-3 p-3 bg-light rounded">
                <h6>Selected File Information:</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Filename:</strong> ${file.name}</p>
                        <p><strong>File Size:</strong> ${(file.size / 1024).toFixed(2)} KB</p>
                        <p><strong>MIME Type:</strong> ${file.type}</p>
                        <div id="image-dimensions">Detecting dimensions...</div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove any existing file info container
        $('.file-info-container').remove();
        
        // Add the file info container after the upload area
        $('#media-upload-area').after(fileInfoHtml);
        
        // If it's an image file, get dimensions
        if (file.type.startsWith('image/')) {
            getImageDimensions(file);
        } else {
            $('#image-dimensions').html('<p><strong>Dimensions:</strong> Not applicable for non-image files</p>');
        }
    }
    
    // Function to get image dimensions
    function getImageDimensions(file) {
        const img = new Image();
        const objectUrl = URL.createObjectURL(file);
        
        img.onload = function() {
            $('#image-dimensions').html(`<p><strong>Dimensions:</strong> ${this.naturalWidth} x ${this.naturalHeight} pixels</p>`);
            URL.revokeObjectURL(objectUrl);
        };
        
        img.onerror = function() {
            $('#image-dimensions').html('<p><strong>Dimensions:</strong> Unable to detect</p>');
            URL.revokeObjectURL(objectUrl);
        };
        
        img.src = objectUrl;
    }
    
    // Show category modal for creating new category
    function showCategoryModal() {
        $('#categoryModalLabel').text('Add New Category');
        $('#categoryForm')[0].reset();
        $('#categoryId').val('');
        $('#categoryMethod').val('');
        $('#category-image-preview').hide();
        $('#category-preview-img').attr('src', '');
    }
    
    // Edit existing category
    function editCategory(id) {
        $.ajax({
            url: '/admin/categories/' + id,
            type: 'GET',
            success: function(data) {
                console.log(data);
                $('#categoryModalLabel').text('Edit Category');
                $('#categoryId').val(data.id);
                $('#categoryMethod').val('PUT');
                $('#categoryName').val(data.name);
                $('#categoryDescription').val(data.description);
                $('#categoryStatus').val(data.is_active ? '1' : '0');
                
                // Show existing image if available
                if (data.image) {
                    $('#category-preview-img').attr('src', data.image_url);
                    $('#category-image-preview').show();
                } else {
                    $('#category-image-preview').hide();
                }
                
                $('#categoryModal').modal('show');
            },
            error: function() {
                alert('Error loading category data.');
            }
        });
    }
    
    // Save category (create or update)
    function saveCategory() {
        const id = $('#categoryId').val();
        const url = id ? '/admin/categories/' + id : '/admin/categories';
        
        // Use FormData to handle file uploads
        const formData = new FormData($('#categoryForm')[0]);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,  // Important for file uploads
            contentType: false,  // Important for file uploads
            success: function(response) {
                if (response.success) {
                    $('#categoryModal').modal('hide');
                    location.reload();
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
                    alert('Error saving category.');
                }
            }
        });
    }
    
    // Delete category
    function deleteCategory(id) {
        if (confirm('Are you sure you want to delete this category? This will also delete all subcategories.')) {
            $.ajax({
                url: '/admin/categories/' + id,
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
    
    // Show subcategories for a category
    function showSubCategories(categoryId) {
        currentCategoryId = categoryId;
        loadSubCategories(categoryId, 1);
        $('#subcategoriesModal').modal('show');
    }
    
    // Load subcategories
    function loadSubCategories(categoryId, page = 1) {
        $.ajax({
            url: '/admin/categories/' + categoryId + '/subcategories?page=' + page,
            type: 'GET',
            success: function(data) {
                // Set parent category name
                const categoryName = data.data[0]?.category?.name || 'Category';
                currentCategoryName = categoryName;
                $('#subcategoryParentName').text('Subcategories for ' + categoryName);
                
                // Populate table
                let html = '';
                if (data.data.length > 0) {
                    data.data.forEach(function(subcategory, index) {
                        html += `
                            <tr>
                                <td>${data.from + index}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        ${subcategory.image ? 
                                            `<img src="${subcategory.image_url}" class="rounded me-3" width="40" height="40" alt="${subcategory.name}" onerror="this.onerror=null;this.src='/images/placeholder.png';" loading="lazy">` :
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
        const categoryName = currentCategoryName || 'Category';
        $('#subcategoryModalLabel').text('Add New Subcategory of ' + categoryName);
        $('#subcategoryForm')[0].reset();
        $('#subcategoryId').val('');
        $('#subcategoryMethod').val('');
        $('#subcategoryCategoryId').val(currentCategoryId);
        $('#subcategory-image-preview').hide();
        $('#subcategory-preview-img').attr('src', '');
    }
    
    // Edit existing subcategory
    function editSubCategory(id) {
        $.ajax({
            url: '/admin/subcategories/' + id,
            type: 'GET',
            success: function(data) {
                const categoryName = data.category?.name || currentCategoryName || 'Category';
                $('#subcategoryModalLabel').text('Edit Subcategory of ' + categoryName);
                $('#subcategoryId').val(data.id);
                $('#subcategoryMethod').val('PUT');
                $('#subcategoryCategoryId').val(data.category_id);
                $('#subcategoryName').val(data.name);
                $('#subcategoryDescription').val(data.description);
                $('#subcategoryStatus').val(data.is_active ? '1' : '0');
                
                // Show existing image if available
                if (data.image) {
                    $('#subcategory-preview-img').attr('src', data.image_url);
                    $('#subcategory-image-preview').show();
                } else {
                    $('#subcategory-image-preview').hide();
                }
                
                // Show the modal
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
        const url = id ? '/admin/subcategories/' + id : '/admin/subcategories';
        
        // Use FormData to handle file uploads
        const formData = new FormData($('#subcategoryForm')[0]);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,  // Important for file uploads
            contentType: false,  // Important for file uploads
            success: function(response) {
                if (response.success) {
                    $('#subcategoryModal').modal('hide');
                    loadSubCategories(currentCategoryId);
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
                url: '/admin/subcategories/' + id,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        loadSubCategories(currentCategoryId);
                    }
                },
                error: function() {
                    alert('Error deleting subcategory.');
                }
            });
        }
    }
    
    // ========================================
    // MEDIA LIBRARY FUNCTIONS REMOVED
    // These functions have been commented out to prevent errors
    // Direct file uploads are now handled inline in forms
    // ========================================
    /*
    // Open media library
    function openMediaLibrary(target) {
        currentMediaTarget = target;
        loadMedia();
        $('#mediaLibraryModal').modal('show');
    }
    
    // Load media items
    function loadMedia(page = 1) {
        ... REMOVED FOR BREVITY ...
    }
    
    // Select media
    function selectMedia(id, url, target) {
        ... REMOVED FOR BREVITY ...
    }
    
    // Remove category image
    function removeCategoryImage() {
        ... REMOVED FOR BREVITY ...
    }
    
    // Remove subcategory image
    function removeSubcategoryImage() {
        ... REMOVED FOR BREVITY ...
    }
    
    // Upload media
    function uploadMedia() {
        ... REMOVED FOR BREVITY ...
    }
    
    // Upload image via drag and drop and select it for category/subcategory
    function uploadAndSelectImage(file, target) {
        ... REMOVED FOR BREVITY ...
    }
    
    // Restore the upload area after failed upload
    function restoreUploadArea(target) {
        ... REMOVED FOR BREVITY ...
    }
    */
    // ========================================
    // END MEDIA LIBRARY FUNCTIONS REMOVED
    // ========================================
    
    // ========================================
    // NEW DIRECT FILE UPLOAD PREVIEW FUNCTIONS
    // ========================================
    
    // Preview category image before upload
    function previewCategoryImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#category-preview-img').attr('src', e.target.result);
                $('#category-image-preview').show();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Preview subcategory image before upload
    function previewSubcategoryImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#subcategory-preview-img').attr('src', e.target.result);
                $('#subcategory-image-preview').show();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // ========================================
    // END DIRECT FILE UPLOAD PREVIEW FUNCTIONS
    // ========================================
    
    /*
    // ========================================
    // MEDIA LIBRARY FUNCTIONS REMOVED
    // These functions made AJAX calls to /admin/media routes that no longer exist
    // Media library functionality has been replaced with direct file uploads
    // ========================================
    
    // Load media items
    function loadMedia(page = 1) {
        const search = $('#mediaSearch').val();
        const params = new URLSearchParams();
        params.append('page', page);
        if (search && search.length > 0) {
            params.append('search', search);
        }
        
        $.ajax({
            url: '/admin/media/list?' + params.toString(),
            type: 'GET',
            success: function(data) {
                let html = '';
                if (data.data.length > 0) {
                    data.data.forEach(function(media) {
                        html += `
                            <div class="col-md-3 mb-3">
                                <div class="border rounded-3 p-2 media-item" onclick="selectMedia(${media.id}, '${media.url}', '${currentMediaTarget}')">
                                    ${media.mime_type.startsWith('image/') ? 
                                        `<img src="${media.url}" class="img-fluid rounded" alt="${media.name}" style="height: 120px; object-fit: cover;">` :
                                        `<div class="d-flex align-items-center justify-content-center" style="height: 120px;">
                                            <i class="fas fa-file fa-2x text-muted"></i>
                                        </div>`
                                    }
                                    <div class="mt-2 text-truncate small">${media.name}</div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html = `
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-image fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No media found</p>
                        </div>
                    `;
                }
                
                $('#mediaLibraryContent').html(html);
                
                // Populate pagination
                if (data.last_page > 1) {
                    let paginationHtml = `
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                ${data.prev_page_url ? 
                                    `<li class="page-item"><a class="page-link rounded-pill" href="javascript:void(0)" onclick="loadMedia(${data.current_page - 1})">Previous</a></li>` :
                                    `<li class="page-item disabled"><span class="page-link rounded-pill">Previous</span></li>`
                                }
                    `;
                    
                    for (let i = 1; i <= data.last_page; i++) {
                        paginationHtml += `
                            <li class="page-item ${i === data.current_page ? 'active' : ''}">
                                <a class="page-link rounded-pill" href="javascript:void(0)" onclick="loadMedia(${i})">${i}</a>
                            </li>
                        `;
                    }
                    
                    paginationHtml += `
                                ${data.next_page_url ? 
                                    `<li class="page-item"><a class="page-link rounded-pill" href="javascript:void(0)" onclick="loadMedia(${data.current_page + 1})">Next</a></li>` :
                                    `<li class="page-item disabled"><span class="page-link rounded-pill">Next</span></li>`
                                }
                            </ul>
                        </nav>
                    `;
                    
                    $('#mediaLibraryPagination').html(paginationHtml);
                } else {
                    $('#mediaLibraryPagination').html('');
                }
            },
            error: function() {
                alert('Error loading media.');
            }
        });
    }
    
    // Select media
    function selectMedia(id, url, target) {
        if (target === 'category') {
            $('#category-image-preview').html(`
                <div class="position-relative">
                    <img src="${url}" class="img-fluid rounded" alt="Selected image" style="max-height: 200px; object-fit: contain;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeCategoryImage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            $('#categoryImageId').val(id);
        } else if (target === 'subcategory') {
            $('#subcategory-image-preview').html(`
                <div class="position-relative">
                    <img src="${url}" class="img-fluid rounded" alt="Selected image" style="max-height: 200px; object-fit: contain;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeSubcategoryImage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            $('#subcategoryImageId').val(id);
        }
        
        $('#mediaLibraryModal').modal('hide');
    }
    
    // Remove category image
    function removeCategoryImage() {
        $('#category-image-preview').html(`
            <div class="py-3">
                <i class="fas fa-image fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-2">No image selected</p>
                <button type="button" class="btn btn-outline-theme btn-sm rounded-pill" onclick="openMediaLibrary('category')">
                    <i class="fas fa-folder-open me-1"></i> Select Image
                </button>
            </div>
        `);
        $('#categoryImageId').val('');
    }
    
    // Remove subcategory image
    function removeSubcategoryImage() {
        $('#subcategory-image-preview').html(`
            <div class="py-3">
                <i class="fas fa-image fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-2">No image selected</p>
                <button type="button" class="btn btn-outline-theme btn-sm rounded-pill" onclick="openMediaLibrary('subcategory')">
                    <i class="fas fa-folder-open me-1"></i> Select Image
                </button>
            </div>
        `);
        $('#subcategoryImageId').val('');
    }
    
    // Upload media
    function uploadMedia() {
        const fileInput = $('#mediaFile')[0];
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Please select a file to upload.');
            return;
        }
        
        const file = fileInput.files[0];
        
        // Validate file type
        const validTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv', 'video/mpeg', 'video/ogg', 'video/webm',
            'application/pdf', 'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain', 'text/csv'
        ];
        
        if (!validTypes.includes(file.type)) {
            alert('Please upload a valid file (JPEG, PNG, GIF, WEBP, MP4, MOV, AVI, WMV, MPEG, OGG, WEBM, PDF, DOC, DOCX, XLSX, PPTX, TXT, CSV).');
            return;
        }
        
        // Validate file size - 25MB limit
        const maxSize = 25 * 1024 * 1024; // 25MB in bytes
        if (file.size > maxSize) {
            alert('File size must be less than 25MB.');
            return;
        }
        
        const formData = new FormData($('#mediaUploadForm')[0]);
        
        // Show loading indicator in upload area
        const uploadArea = $('#media-upload-area');
        const uploadContent = $('#media-upload-content');
        const originalContent = uploadContent.html();
        uploadContent.html('<i class="fas fa-spinner fa-spin text-theme mb-2" style="font-size: 24px;"></i><p class="text-muted mb-0 small">Uploading...</p>');
        uploadArea.css('pointer-events', 'none');
        
        // Remove file info display during upload
        $('.file-info-container').remove();
        
        $.ajax({
            url: '/admin/media',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Reset form
                    $('#mediaUploadForm')[0].reset();
                    
                    // Reload media library
                    loadMedia();
                    
                } else {
                    alert('Error uploading file: ' + (response.error || 'Unknown error'));
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
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    alert('Error uploading media: ' + xhr.responseJSON.error);
                } else {
                    alert('Error uploading media. Please try again.');
                }
            },
            complete: function() {
                // Restore upload area state
                uploadContent.html(originalContent);
                uploadArea.css('pointer-events', 'auto');
            }
        });
    }
    
    // Upload image via drag and drop and select it for category/subcategory
    function uploadAndSelectImage(file, target) {
        // Validate file type
        const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validImageTypes.includes(file.type)) {
            alert('Please upload a valid image file (JPEG, PNG, GIF, or WEBP).');
            return;
        }
        
        // Validate file size - 25MB limit
        const maxSize = 25 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('Image size must be less than 25MB.');
            return;
        }
        
        // Get the preview container based on target
        const previewContainer = target === 'category' ? '#category-image-preview' : '#subcategory-image-preview';
        
        // Show loading indicator in the preview area
        $(previewContainer).html(`
            <div class="upload-progress-overlay">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x text-theme mb-2"></i>
                    <p class="mb-0 text-muted">Uploading image...</p>
                </div>
            </div>
        `);
        
        // Prepare form data
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        $.ajax({
            url: '/admin/media',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success && response.media) {
                    // Use the existing selectMedia function to set the image
                    selectMedia(response.media.id, response.media.url, target);
                } else {
                    // Restore the upload area on error
                    restoreUploadArea(target);
                    alert('Error uploading image: ' + (response.error || 'Unknown error'));
                }
            },
            error: function(xhr) {
                // Restore the upload area on error
                restoreUploadArea(target);
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    for (let field in errors) {
                        errorMessages += errors[field].join(', ') + '\n';
                    }
                    alert('Validation errors:\n' + errorMessages);
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    alert('Error uploading image: ' + xhr.responseJSON.error);
                } else {
                    alert('Error uploading image. Please try again.');
                }
            }
        });
    }
    
    // Restore the upload area after failed upload
    function restoreUploadArea(target) {
        const previewContainer = target === 'category' ? '#category-image-preview' : '#subcategory-image-preview';
        const uploadAreaId = target === 'category' ? 'category-image-upload-area' : 'subcategory-image-upload-area';
        
        $(previewContainer).html(`
            <div class="upload-area" id="${uploadAreaId}" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div>
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('${target}')">
                        <i class="fas fa-folder-open me-1"></i> Select from Media Library
                    </button>
                </div>
            </div>
        `);
    }
    
    // ========================================
    // END MEDIA LIBRARY FUNCTIONS REMOVED
    // ========================================
    */
</script>
@endsection
