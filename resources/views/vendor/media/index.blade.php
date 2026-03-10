@extends('vendor.layouts.app')

@section('title', 'Media Library')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Media Library'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Upload Media</h5>
                            </div>
                            <div class="card-body">
                                <form id="mediaUploadForm">
                                    @csrf
                                    <div class="upload-area" id="media-upload-area" style="min-height: 150px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                        <div class="text-center" id="media-upload-content">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0 small">Drag & drop files here or click to upload</p>
                                        </div>
                                    </div>
                                    <input type="file" class="form-control d-none" id="mediaFile" name="file" accept="image/*,video/*,application/pdf">
                                </form>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Filter</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Type</label>
                                    <select class="form-select rounded-pill" id="mediaTypeFilter">
                                        <option value="all">All Files</option>
                                        <option value="images" selected>Images</option>
                                        <option value="videos">Videos</option>
                                        <option value="documents">Documents</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Search</label>
                                    <input type="text" class="form-control rounded-pill" id="mediaSearch" placeholder="Search files...">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 fw-bold">Media Files</h5>
                                    <span class="badge bg-light text-dark" id="mediaCount">0 files</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="mediaLibraryContent" class="row">
                                    <!-- Media items will be loaded here -->
                                </div>
                                
                                <div id="mediaLibraryPagination" class="d-flex justify-content-center mt-4">
                                    <!-- Pagination will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>

<!-- Media Details Modal -->
<div class="modal fade" id="mediaDetailsModal" tabindex="-1" aria-labelledby="mediaDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaDetailsModalLabel">Media Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div id="mediaPreview" class="text-center mb-3">
                            <!-- Preview will be loaded here -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">File Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted">File Name:</td>
                                <td id="mediaFileName">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Type:</td>
                                <td id="mediaMimeType">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Size:</td>
                                <td id="mediaSize">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">URL:</td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" id="mediaUrl" readonly>
                                </td>
                            </tr>
                        </table>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="copyMediaUrl()">
                            <i class="fas fa-copy me-1"></i> Copy URL
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger rounded-pill" id="deleteMediaBtn">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    #media-upload-area.drag-over {
        border-color: var(--theme-color, #FF6B00) !important;
        background-color: rgba(255, 107, 0, 0.05);
    }
    
    #media-upload-area {
        transition: all 0.2s ease;
    }
    
    #media-upload-area:hover {
        border-color: var(--theme-color, #FF6B00);
        background-color: rgba(255, 107, 0, 0.03);
    }
    
    .media-item {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    
    .media-item:hover {
        border-color: var(--theme-color, #FF6B00);
        transform: translateY(-2px);
    }
    
    .media-item .media-thumbnail {
        height: 120px;
        object-fit: cover;
        width: 100%;
    }
    
    .media-item .media-icon {
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
    }
</style>
@endsection

@section('scripts')
<script>
    // Base URL for AJAX requests
    const baseUrl = '{{ url('/') }}';
    
    let currentMediaId = null;
    
    $(document).ready(function() {
        // Load media on page load
        loadMedia(1);
        
        // Click handler for upload area
        $('#media-upload-area').on('click', function() {
            $('#mediaFile').click();
        });
        
        // File input change handler
        $('#mediaFile').on('change', function() {
            if (this.files && this.files[0]) {
                uploadMedia();
            }
        });
        
        // Drag and drop handlers
        $('#media-upload-area').on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $('#media-upload-area').on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        });
        
        $('#media-upload-area').on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                $('#mediaFile')[0].files = files;
                uploadMedia();
            }
        });
        
        // Filter change handlers
        $('#mediaTypeFilter').on('change', function() {
            loadMedia(1);
        });
        
        // Search with debounce
        let searchTimeout;
        $('#mediaSearch').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                loadMedia(1);
            }, 300);
        });
        
        // Delete media button
        $('#deleteMediaBtn').on('click', function() {
            if (currentMediaId && confirm('Are you sure you want to delete this file?')) {
                deleteMedia(currentMediaId);
            }
        });
    });
    
    function loadMedia(page = 1) {
        const searchTerm = $('#mediaSearch').val() || '';
        const type = $('#mediaTypeFilter').val() || 'all';
        
        $.ajax({
            url: baseUrl + '/vendor/media/list',
            type: 'GET',
            data: {
                page: page,
                search: searchTerm,
                type: type
            },
            success: function(data) {
                let html = '';
                
                // Update count
                $('#mediaCount').text(data.total + ' files');
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(function(media) {
                        const isImage = media.mime_type && media.mime_type.startsWith('image/');
                        const isVideo = media.mime_type && media.mime_type.startsWith('video/');
                        
                        html += `
                            <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                                <div class="media-item card h-100" onclick="showMediaDetails(${media.id}, '${media.url}', '${media.name || media.file_name}', '${media.mime_type}', ${media.size})">
                                    ${isImage ? 
                                        `<img src="${media.url}" class="card-img-top media-thumbnail" alt="${media.name || media.file_name}">` :
                                        `<div class="media-icon">
                                            <i class="fas ${isVideo ? 'fa-video' : 'fa-file'} fa-3x text-muted"></i>
                                        </div>`
                                    }
                                    <div class="card-body p-2">
                                        <p class="small mb-0 text-truncate" title="${media.name || media.file_name}">${media.name || media.file_name}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html = `
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-images fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No media found</p>
                            <p class="text-muted small">Upload some files to get started</p>
                        </div>
                    `;
                }
                
                $('#mediaLibraryContent').html(html);
                
                // Pagination
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
                        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                            paginationHtml += `
                                <li class="page-item ${i === data.current_page ? 'active' : ''}">
                                    <a class="page-link rounded-pill" href="javascript:void(0)" onclick="loadMedia(${i})">${i}</a>
                                </li>
                            `;
                        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                            paginationHtml += `<li class="page-item disabled"><span class="page-link rounded-pill">...</span></li>`;
                        }
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
                $('#mediaLibraryContent').html(`
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <p class="text-danger">Error loading media</p>
                    </div>
                `);
            }
        });
    }
    
    function uploadMedia() {
        const formData = new FormData($('#mediaUploadForm')[0]);
        
        // Show loading state
        $('#media-upload-content').html(`
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Uploading...</span>
            </div>
            <p class="text-muted mb-0 small mt-2">Uploading...</p>
        `);
        
        $.ajax({
            url: baseUrl + '/vendor/media',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Reset upload area
                $('#media-upload-content').html(`
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0 small">Drag & drop files here or click to upload</p>
                `);
                $('#mediaFile').val('');
                
                if (response.success) {
                    // Reload media library
                    loadMedia(1);
                }
            },
            error: function(xhr) {
                // Reset upload area
                $('#media-upload-content').html(`
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0 small">Drag & drop files here or click to upload</p>
                `);
                $('#mediaFile').val('');
                
                let errorMessage = 'Error uploading file';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                alert(errorMessage);
            }
        });
    }
    
    function showMediaDetails(id, url, name, mimeType, size) {
        currentMediaId = id;
        
        const isImage = mimeType && mimeType.startsWith('image/');
        const isVideo = mimeType && mimeType.startsWith('video/');
        
        // Set preview
        if (isImage) {
            $('#mediaPreview').html(`<img src="${url}" class="img-fluid rounded" alt="${name}" style="max-height: 300px;">`);
        } else if (isVideo) {
            $('#mediaPreview').html(`<video src="${url}" class="img-fluid rounded" controls style="max-height: 300px;"></video>`);
        } else {
            $('#mediaPreview').html(`<div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;"><i class="fas fa-file fa-4x text-muted"></i></div>`);
        }
        
        // Set details
        $('#mediaFileName').text(name);
        $('#mediaMimeType').text(mimeType);
        $('#mediaSize').text(formatFileSize(size));
        $('#mediaUrl').val(url);
        
        $('#mediaDetailsModal').modal('show');
    }
    
    function copyMediaUrl() {
        const urlInput = document.getElementById('mediaUrl');
        urlInput.select();
        document.execCommand('copy');
        
        // Show feedback
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
        setTimeout(function() {
            btn.innerHTML = originalText;
        }, 2000);
    }
    
    function deleteMedia(id) {
        $.ajax({
            url: baseUrl + '/vendor/media/' + id,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#mediaDetailsModal').modal('hide');
                    loadMedia(1);
                }
            },
            error: function() {
                alert('Error deleting file');
            }
        });
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
</script>
@endsection
