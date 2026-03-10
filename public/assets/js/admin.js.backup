// Common JavaScript functions for the admin panel
// Wrap the entire code in a function that checks for jQuery
(function() {
    // Wait for jQuery to be available
    function waitForjQuery(callback) {
        if (typeof window.jQuery !== 'undefined') {
            callback();
        } else {
            setTimeout(function() {
                waitForjQuery(callback);
            }, 50);
        }
    }
    
    // Initialize when jQuery is ready
    waitForjQuery(function() {
        $(document).ready(function() {
            
            // ========================================
            // MOBILE SIDEBAR TOGGLE FUNCTIONALITY
            // ========================================
            
            const $sidebar = $('#sidebar');
            const $sidebarOverlay = $('#sidebar-overlay');
            const $sidebarToggle = $('#sidebar-toggle');
            const $sidebarClose = $('#sidebar-close');
            
            // Function to open sidebar
            function openSidebar() {
                $sidebar.addClass('show');
                $sidebarOverlay.addClass('show');
                $('body').css('overflow', 'hidden'); // Prevent body scroll when sidebar is open
            }
            
            // Function to close sidebar
            function closeSidebar() {
                $sidebar.removeClass('show');
                $sidebarOverlay.removeClass('show');
                $('body').css('overflow', ''); // Restore body scroll
            }
            
            // Toggle sidebar on hamburger button click
            $sidebarToggle.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if ($sidebar.hasClass('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
            
            // Close sidebar on close button click
            $sidebarClose.on('click', function(e) {
                e.preventDefault();
                closeSidebar();
            });
            
            // Close sidebar on overlay click
            $sidebarOverlay.on('click', function() {
                closeSidebar();
            });
            
            // Close sidebar on nav link click (mobile only)
            $sidebar.find('.nav-link').on('click', function() {
                if ($(window).width() < 768) {
                    closeSidebar();
                }
            });
            
            // Close sidebar on escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $sidebar.hasClass('show')) {
                    closeSidebar();
                }
            });
            
            // Handle window resize - close sidebar if resizing to desktop
            $(window).on('resize', function() {
                if ($(window).width() >= 768) {
                    closeSidebar();
                }
            });
            
            // Handle swipe gestures for mobile
            let touchStartX = 0;
            let touchEndX = 0;
            
            document.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            
            document.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, { passive: true });
            
            function handleSwipe() {
                const swipeThreshold = 50;
                const swipeDistance = touchEndX - touchStartX;
                
                // Swipe right from left edge to open sidebar
                if (touchStartX < 30 && swipeDistance > swipeThreshold && !$sidebar.hasClass('show')) {
                    openSidebar();
                }
                
                // Swipe left to close sidebar
                if (swipeDistance < -swipeThreshold && $sidebar.hasClass('show')) {
                    closeSidebar();
                }
            }
            
            // ========================================
            // END MOBILE SIDEBAR TOGGLE FUNCTIONALITY
            // ========================================
            
            // ========================================
            // DESKTOP SIDEBAR TOGGLE FUNCTIONALITY
            // ========================================
            
            const $desktopSidebarToggle = $('#desktop-sidebar-toggle');
            const $mainContent = $('main.main-content');
            
            // Check localStorage for saved sidebar state
            const savedSidebarState = localStorage.getItem('sidebarCollapsed');
            
            if (savedSidebarState === 'true' && $(window).width() >= 768) {
                $sidebar.addClass('collapsed');
                $mainContent.addClass('sidebar-collapsed');
                $desktopSidebarToggle.find('i').removeClass('fa-bars').addClass('fa-angles-right');
            }
            
            // Desktop sidebar toggle click handler
            $desktopSidebarToggle.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isCollapsed = $sidebar.hasClass('collapsed');
                
                if (isCollapsed) {
                    // Expand sidebar
                    $sidebar.removeClass('collapsed');
                    $mainContent.removeClass('sidebar-collapsed');
                    $(this).find('i').removeClass('fa-angles-right').addClass('fa-bars');
                    localStorage.setItem('sidebarCollapsed', 'false');
                } else {
                    // Collapse sidebar
                    $sidebar.addClass('collapsed');
                    $mainContent.addClass('sidebar-collapsed');
                    $(this).find('i').removeClass('fa-bars').addClass('fa-angles-right');
                    localStorage.setItem('sidebarCollapsed', 'true');
                }
            });
            
            // ========================================
            // END DESKTOP SIDEBAR TOGGLE FUNCTIONALITY
            // ========================================
            
            // Handle stock status toggle
            function handleStockStatusToggle() {
                const $inStockCheckbox = $('#in_stock');
                const $stockQuantityContainer = $('#stock_quantity_container');
                const $stockStatusText = $('#stock-status-text');
                
                if ($inStockCheckbox.length && $stockQuantityContainer.length) {
                    // Initial state
                    if ($inStockCheckbox.is(':checked')) {
                        $stockQuantityContainer.removeClass('d-none');
                        if ($stockStatusText.length) {
                            $stockStatusText.text('In Stock');
                        }
                    } else {
                        $stockQuantityContainer.addClass('d-none');
                        if ($stockStatusText.length) {
                            $stockStatusText.text('Out of Stock');
                        }
                        // Clear the stock quantity when unchecked
                        $('#stock_quantity').val('');
                    }
                    
                    // Add event listener
                    $inStockCheckbox.on('change', function() {
                        if ($(this).is(':checked')) {
                            $stockQuantityContainer.removeClass('d-none');
                            if ($stockStatusText.length) {
                                $stockStatusText.text('In Stock');
                            }
                        } else {
                            $stockQuantityContainer.addClass('d-none');
                            if ($stockStatusText.length) {
                                $stockStatusText.text('Out of Stock');
                            }
                            // Clear the stock quantity when unchecked
                            $('#stock_quantity').val('');
                        }
                    });
                }
            }
            
            // Initialize stock status toggle
            handleStockStatusToggle();
            
            // Toggle SEO settings
            $('#toggle-seo-settings').on('click', function() {
                const $seoContent = $('#seo-settings-content');
                const $icon = $(this).find('i');
                
                if ($seoContent.hasClass('d-none')) {
                    $seoContent.removeClass('d-none');
                    $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    $(this).html('<i class="fas fa-chevron-up me-1"></i> Collapse');
                } else {
                    $seoContent.addClass('d-none');
                    $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    $(this).html('<i class="fas fa-chevron-down me-1"></i> Expand');
                }
            });
            
            // Category functionality
            function initializeCategorySelection() {
                // Handle initial state of category checkboxes
                $('.category-checkbox:checked').each(function() {
                    const categoryId = $(this).val();
                    const $subcategoryContainer = $('#subcategory_container_' + categoryId);
                    // Show subcategories for checked categories
                    $subcategoryContainer.removeClass('d-none');
                });
                
                // Handle category checkbox changes
                $('.category-checkbox').on('change', function() {
                    const categoryId = $(this).val();
                    const $subcategoryContainer = $('#subcategory_container_' + categoryId);
                    
                    if ($(this).is(':checked')) {
                        // Show subcategories when category is selected
                        $subcategoryContainer.removeClass('d-none');
                    } else {
                        // Hide and deselect subcategories when category is deselected
                        $subcategoryContainer.addClass('d-none');
                        $subcategoryContainer.find('.subcategory-checkbox').prop('checked', false);
                    }
                });
                
                // Handle "Manage Categories & Subcategories" button
                $('#manage-categories-btn').on('click', function() {
                    loadCategoriesForManagement();
                    $('#categoryManagementModal').modal('show');
                });
                
                // Handle parent category selection for subcategories
                $('#subcategory-parent-category').on('change', function() {
                    const categoryId = $(this).val();
                    if (categoryId) {
                        $('#add-subcategory-btn').prop('disabled', false);
                        loadSubcategoriesForManagement(categoryId);
                    } else {
                        $('#add-subcategory-btn').prop('disabled', true);
                        $('#subcategories-list').html('');
                    }
                });
                
                // Handle adding new category
                $('#add-category-btn').on('click', function() {
                    const categoryName = $('#new-category-name').val().trim();
                    
                    if (!categoryName) {
                        alert('Please enter a category name');
                        return;
                    }
                    
                    // Make AJAX call to create the category
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    
                    $.ajax({
                        url: '/admin/categories/create',
                        method: 'POST',
                        data: {
                            name: categoryName,
                            description: ''
                        },
                        success: function(response) {
                            if (response.success) {
                                
                                // Clear the input
                                $('#new-category-name').val('');
                                
                                // Reload categories
                                loadCategoriesForManagement();
                                
                                // Also update the main category selection area
                                updateMainCategorySelection(response.category);
                            } else {
                                alert('Error creating category');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMessages = '';
                                for (let field in errors) {
                                    errorMessages += errors[field][0] + '\n';
                                }
                                alert('Error creating category:\n' + errorMessages);
                            } else {
                                alert('Error creating category');
                            }
                        }
                    });
                });
                
                // Handle adding new subcategory
                $('#add-subcategory-btn').on('click', function() {
                    const subcategoryName = $('#new-subcategory-name').val().trim();
                    const parentCategoryId = $('#subcategory-parent-category').val();
                    
                    if (!subcategoryName) {
                        alert('Please enter a subcategory name');
                        return;
                    }
                    
                    if (!parentCategoryId) {
                        alert('Please select a parent category');
                        return;
                    }
                    
                    // Make AJAX call to create the subcategory
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    
                    $.ajax({
                        url: '/admin/subcategories/create',
                        method: 'POST',
                        data: {
                            category_id: parentCategoryId,
                            name: subcategoryName,
                            description: ''
                        },
                        success: function(response) {
                            if (response.success) {
                                
                                // Clear the input
                                $('#new-subcategory-name').val('');
                                
                                // Reload subcategories
                                loadSubcategoriesForManagement(parentCategoryId);
                                
                                // Also update the main subcategory selection area
                                updateMainSubcategorySelection(response.subcategory);
                            } else {
                                alert('Error creating subcategory');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMessages = '';
                                for (let field in errors) {
                                    errorMessages += errors[field][0] + '\n';
                                }
                                alert('Error creating subcategory:\n' + errorMessages);
                            } else {
                                alert('Error creating subcategory');
                            }
                        }
                    });
                });
                
                // Handle saving category selections
                $('#save-category-selections').on('click', function() {
                    // Close the modal
                    $('#categoryManagementModal').modal('hide');
                });
            }
            
            // Load categories for management modal
            function loadCategoriesForManagement() {
                // Make AJAX call to load categories
                $.ajax({
                    url: '/admin/categories-all',
                    method: 'GET',
                    success: function(categories) {
                        let html = '';
                        if (categories.length > 0) {
                            categories.forEach(function(category) {
                                html += `
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="manage_category_${category.id}" value="${category.id}" data-category-name="${category.name}">
                                        <label class="form-check-label" for="manage_category_${category.id}">
                                            ${category.name}
                                        </label>
                                    </div>
                                `;
                            });
                        } else {
                            html = '<p class="text-muted">No categories available</p>';
                        }
                        
                        $('#categories-list').html(html);
                        
                        // Update the parent category dropdown for subcategories
                        let dropdownHtml = '<option value="">Select a category first</option>';
                        categories.forEach(function(category) {
                            dropdownHtml += `<option value="${category.id}">${category.name}</option>`;
                        });
                        $('#subcategory-parent-category').html(dropdownHtml);
                    },
                    error: function() {
                        $('#categories-list').html('<p class="text-danger">Error loading categories</p>');
                    }
                });
            }
            
            // Load subcategories for management modal
            function loadSubcategoriesForManagement(categoryId) {
                // Make AJAX call to load subcategories for the selected category
                $.ajax({
                    url: '/admin/categories/' + categoryId + '/subcategories',
                    method: 'GET',
                    success: function(response) {
                        let html = '';
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(subcategory) {
                                html += `
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="manage_subcategory_${subcategory.id}" value="${subcategory.id}">
                                        <label class="form-check-label" for="manage_subcategory_${subcategory.id}">
                                            ${subcategory.name}
                                        </label>
                                    </div>
                                `;
                            });
                        } else {
                            html = '<p class="text-muted">No subcategories available</p>';
                        }
                        
                        $('#subcategories-list').html(html);
                    },
                    error: function() {
                        $('#subcategories-list').html('<p class="text-danger">Error loading subcategories</p>');
                    }
                });
            }
            
            // Update main category selection area with new category
            function updateMainCategorySelection(category) {
                // Check if the category already exists in the main selection area
                if ($('#category_' + category.id).length === 0) {
                    const categoryHtml = `
                        <div class="form-check mb-2 category-item" data-category-id="${category.id}">
                            <input class="form-check-input category-checkbox" type="checkbox" id="category_${category.id}" value="${category.id}" name="product_categories[${category.id}][category_id]">
                            <label class="form-check-label fw-bold" for="category_${category.id}">
                                ${category.name}
                            </label>
                            <div class="subcategory-container ms-4 mt-2 d-none" id="subcategory_container_${category.id}"></div>
                        </div>
                    `;
                    
                    // Add the new category to the main selection area
                    $('#category-selection').append(categoryHtml);
                    
                    // Reattach event handlers
                    $('.category-checkbox').off('change').on('change', function() {
                        const categoryId = $(this).val();
                        const $subcategoryContainer = $('#subcategory_container_' + categoryId);
                        
                        if ($(this).is(':checked')) {
                            // Show subcategories when category is selected
                            $subcategoryContainer.removeClass('d-none');
                        } else {
                            // Hide and deselect subcategories when category is deselected
                            $subcategoryContainer.addClass('d-none');
                            $subcategoryContainer.find('.subcategory-checkbox').prop('checked', false);
                        }
                    });
                }
            }
            
            // Update main subcategory selection area with new subcategory
            function updateMainSubcategorySelection(subcategory) {
                // Check if the subcategory already exists in the main selection area
                if ($('#subcategory_' + subcategory.id).length === 0) {
                    const subcategoryHtml = `
                        <div class="form-check mb-1">
                            <input class="form-check-input subcategory-checkbox" type="checkbox" id="subcategory_${subcategory.id}" value="${subcategory.id}" name="product_categories[${subcategory.category_id}][subcategory_ids][]" data-category-id="${subcategory.category_id}">
                            <label class="form-check-label" for="subcategory_${subcategory.id}">
                                ${subcategory.name}
                            </label>
                        </div>
                    `;
                    
                    // Add the new subcategory to the main selection area
                    $('#subcategory_container_' + subcategory.category_id).append(subcategoryHtml);
                }
            }
            
            // Initialize category selection
            initializeCategorySelection();
            
            // Media library functionality
            let selectedMedia = [];
            let targetField = null; // 'main_photo' or 'gallery'
            let hasMorePages = false;
            let currentPage = 1;
            let currentSearch = '';
            let currentFilter = 'all';

            // Store drag drop initializer reference for later use
            var initializeMainPhotoDragDropRef = null;
            
            // Initialize main photo remove button if it exists
            function initializeMainPhotoRemove() {
                $('#remove-main-photo').off('click').on('click', function() {
                    $('#main_photo_id').val(null); // Set to null instead of empty string
                    $('#main-photo-preview').html(`
                        <div class="upload-area" id="main-photo-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <div>
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#mediaLibraryModal" data-target="main_photo">
                                    <i class="fas fa-folder-open me-1"></i> Select from Media Library
                                </button>
                            </div>
                        </div>
                    `);
                    // Reinitialize drag and drop for the new upload area
                    if (initializeMainPhotoDragDropRef) {
                        initializeMainPhotoDragDropRef();
                    }
                });
            }
            
            // Call on page load to handle existing remove button in edit view
            $(document).ready(function() {
                initializeMainPhotoRemove();
            });

            // Handle media library modal show event
            $('#mediaLibraryModal').on('show.bs.modal', function(event) {
                targetField = $(event.relatedTarget).data('target');
                selectedMedia = [];
                $('#select-media-btn').prop('disabled', true);
                
                // Reset to first page
                currentPage = 1;
                currentSearch = '';
                currentFilter = 'all';
                $('#media-search').val('');
                $('#media-filter').val('all');
                
                // Load media items
                loadMediaLibrary();
            });
            
            // Search media with debounce
            let searchTimeout;
            $('#media-search').on('input', function() {
                clearTimeout(searchTimeout);
                currentSearch = $(this).val();
                currentPage = 1;
                
                searchTimeout = setTimeout(function() {
                    loadMediaLibrary();
                }, 300); // 300ms debounce
            });
            
            // Filter media
            $('#media-filter').on('change', function() {
                currentFilter = $(this).val();
                currentPage = 1;
                loadMediaLibrary();
            });
            
            // Upload first media button (for empty state)
            $(document).on('click', '#empty-state-upload', function() {
                // Create a hidden file input that accepts multiple files
                const fileInput = $('<input type="file" accept="image/*,video/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" multiple style="display: none;">');
                $('body').append(fileInput);
                
                fileInput.on('change', function() {
                    if (this.files.length > 0) {
                        // Handle multiple file uploads
                        for (let i = 0; i < this.files.length; i++) {
                            handleFileUpload(this.files[i]);
                        }
                    }
                    fileInput.remove();
                });
                
                fileInput.click();
            });
            
            // NOTE: Drag and drop functionality for media library is handled in media/index.blade.php
            // to avoid duplicate event handlers causing double uploads
            
            // Handle file upload
            function handleFileUpload(file) {
                // Debug: Log file details when function is called
                console.log('handleFileUpload called with file:', file);
                
                // Validate file type
                const validTypes = [
                    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                    'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv', 'video/mpeg', 'video/ogg',
                    'application/pdf', 'application/msword', 
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'text/plain', 'text/csv'
                ];
                if (!validTypes.includes(file.type)) {
                    alert('Please upload a valid file (JPEG, PNG, GIF, WEBP, MP4, MOV, AVI, WMV, MPEG, OGG, PDF, DOC, DOCX, XLSX, PPTX, TXT, CSV).');
                    return;
                }
                
                // Validate file size - Increase limits to 25MB for all file types
                const maxSize = 25 * 1024 * 1024; // 25MB in bytes
                if (file.size > maxSize) {
                    alert('File size must be less than 25MB.');
                    return;
                }
                
                // Debug: Log file details before creating FormData
                console.log('File details before FormData:', file);
                console.log('File name:', file.name);
                console.log('File size:', file.size);
                console.log('File type:', file.type);
                
                const formData = new FormData();
                formData.append('file', file);
                formData.append('name', file.name);
                
                // Debug: Log FormData contents
                console.log('FormData created with file:', formData);
                
                // Debug: Log all FormData entries
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ', ' + pair[1]);
                }
                
                // Show upload indicator only during actual upload
                const $uploadIndicator = $('<div class="mb-4"><div class="card border-0 shadow-sm h-100"><div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Uploading...</span></div></div></div></div>');
                $('#media-library-items').prepend($uploadIndicator);
                
                // Instead of hiding existing content, we'll just add the new item at the top
                // This prevents the blinking/flickering effect
                // $('#media-library-items .mb-4:not(:first-child)').addClass('d-none');
                // $('#no-media-message').addClass('d-none');
                
                // Debug: Log CSRF token
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                console.log('CSRF Token:', csrfToken);
                
                // Send AJAX request to upload media
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                $.ajax({
                    url: '/admin/media',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                const percentComplete = evt.loaded / evt.total * 100;
                                // We could update a progress bar here if needed
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(data) {
                        // Remove upload indicator
                        $uploadIndicator.remove();
                        
                        // No need to show existing content again since we never hid it
                        // $('#media-library-items .mb-4').removeClass('d-none');
                        
                        if (data.success) {
                            // Add the new item to the top of the grid without full refresh
                            if (data.media) {
                                const newItem = `
                                    <div class="mb-4">
                                        <div class="card border-0 shadow-sm media-item position-relative h-100" data-id="${data.media.id}">
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center">
                                                <img src="${data.media.url || ''}" alt="${data.media.name || 'Media item'}" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-image fa-2x text-muted\\'></i>'">
                                            </div>
                                            <div class="selection-indicator">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-media-btn" data-id="${data.media.id}" title="Remove">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                `;
                                const $newItem = $(newItem);
                                
                                // Add click event for selection
                                $newItem.find('.media-item').on('click', function(e) {
                                    // Prevent click when clicking on remove button
                                    if ($(e.target).hasClass('remove-media-btn') || $(e.target).closest('.remove-media-btn').length > 0) {
                                        return;
                                    }
                                    
                                    const id = parseInt($(this).data('id'));
                                    const index = selectedMedia.indexOf(id);
                                    
                                    if (index > -1) {
                                        // Already selected, so deselect
                                        $(this).removeClass('border-primary');
                                        selectedMedia.splice(index, 1);
                                    } else {
                                        // Not selected, so select
                                        if (targetField === 'main_photo') {
                                            // For main photo, only allow one selection
                                            $('.media-item').removeClass('border-primary');
                                            selectedMedia = [id];
                                            $(this).addClass('border-primary');
                                        } else {
                                            // For gallery, allow multiple selections
                                            selectedMedia.push(id);
                                            $(this).addClass('border-primary');
                                        }
                                    }
                                    
                                    // Update select button state
                                    $('#select-media-btn').prop('disabled', selectedMedia.length === 0);
                                });
                                
                                // Add click event for remove button
                                $newItem.find('.remove-media-btn').on('click', function(e) {
                                    e.stopPropagation();
                                    const mediaId = $(this).data('id');
                                    removeMedia(mediaId, $(this).closest('.mb-4'));
                                });
                                
                                $('#media-library-items').prepend($newItem);
                                
                                // If this was the first item, hide the no-media message
                                $('#no-media-message').addClass('d-none');
                            }
                        } else {
                            // Upload failed
                            // Show detailed error message to user
                            let errorMessage = 'Upload failed';
                            if (data.errors) {
                                // Handle validation errors
                                errorMessage += ': ' + Object.values(data.errors).flat().join(', ');
                            } else if (data.error) {
                                errorMessage += ': ' + data.error;
                            } else {
                                errorMessage += ': Unknown error occurred';
                            }
                            alert(errorMessage);
                            
                            // Preserve selected media state - don't clear selections on error
                            // The selectedMedia array and UI selections should remain unchanged
                        }
                    },
                    error: function(xhr, status, error) {
                        // Remove upload indicator
                        $uploadIndicator.remove();
                        
                        // No need to show existing content again since we never hid it
                        // $('#media-library-items .mb-4').removeClass('d-none');
                        
                        // Upload failed
                        // Show detailed error message to user
                        let errorMessage = 'Upload failed';
                        
                        // Check if we have a responseJSON with validation errors
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            // Handle validation errors
                            errorMessage += ': ' + Object.values(xhr.responseJSON.errors).flat().join(', ');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            // Handle Laravel error messages
                            errorMessage += ': ' + xhr.responseJSON.message;
                        } else if (xhr.statusText) {
                            // Handle HTTP status text
                            errorMessage += ': ' + xhr.statusText;
                        } else {
                            errorMessage += ': ' + error;
                        }
                        
                        alert(errorMessage);
                        
                        // Preserve selected media state - don't clear selections on error
                        // The selectedMedia array and UI selections should remain unchanged
                    }
                });
            }
            
            // Upload media form submission
            $('#upload-media-btn').on('click', function() {
                // Create a hidden file input that accepts multiple files
                const fileInput = $('<input type="file" accept="image/*,video/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/csv" multiple style="display: none;">');
                $('body').append(fileInput);
                
                fileInput.on('change', function() {
                    if (this.files.length > 0) {
                        // Handle multiple file uploads
                        for (let i = 0; i < this.files.length; i++) {
                            handleFileUpload(this.files[i]);
                        }
                    }
                    fileInput.remove();
                });
                
                fileInput.click();
            });
            

            // Load media library function
            function loadMediaLibrary(page = 1) {
                // Show loading indicator
                $('#media-loading').removeClass('d-none');
                $('#no-media-message').addClass('d-none');
                $('#load-more-btn').addClass('d-none');
                
                // Build URL with parameters - explicitly check for valid page number
                if (!page || page < 1) {
                    page = 1;
                }
                
                let url = '/admin/media/list?page=' + page;
                if (currentSearch) {
                    url += '&search=' + encodeURIComponent(currentSearch);
                }
                if (currentFilter && currentFilter !== 'all') {
                    url += '&type=' + currentFilter;
                }
                
                // Make AJAX request to fetch media items
                $.get(url)
                    .done(function(data) {
                        $('#media-loading').addClass('d-none');
                        
                        if (data.data && data.data.length > 0) {
                            renderMediaItems(data.data);
                            
                            // Check if there are more pages
                            hasMorePages = data.next_page_url !== null;
                            
                            // Show load more button if there are more pages
                            if (hasMorePages) {
                                $('#load-more-btn').removeClass('d-none');
                            }
                        } else {
                            $('#no-media-message').removeClass('d-none');
                        }
                    })
                    .fail(function(xhr, status, error) {
                        $('#media-loading').addClass('d-none');
                        $('#no-media-message').removeClass('d-none');
                        // Error loading media
                        // Request URL error
                    });
            }
            
            // Render media items function with better fallbacks
            function renderMediaItems(mediaItems) {
                const $container = $('#media-library-items');
                
                // If this is the first page, clear the container
                if (currentPage === 1) {
                    $container.empty();
                }
                
                mediaItems.forEach(function(item) {
                    // Ensure item has required properties
                    if (!item || !item.id) {
                        return;
                    }
                    
                    const $col = $('<div class="mb-4"></div>');
                    
                    // Determine the appropriate preview based on file type
                    let previewHtml = '';
                    
                    // Check if MIME type exists and handle different file types
                    if (item.mime_type) {
                        if (item.mime_type.startsWith('image/')) {
                            // Image preview
                            previewHtml = `<img src="${item.url || ''}" alt="${item.name || 'Media item'}" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-image fa-2x text-muted\\'></i>'">`;
                        } else if (item.mime_type === 'application/pdf') {
                            // PDF preview
                            previewHtml = '<i class="fas fa-file-pdf fa-3x text-danger"></i>';
                        } else if (item.mime_type === 'application/msword' || 
                                   item.mime_type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                            // DOC/DOCX preview
                            previewHtml = '<i class="fas fa-file-word fa-3x text-primary"></i>';
                        } else if (item.mime_type === 'application/vnd.ms-excel' || 
                                   item.mime_type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                            // XLS/XLSX preview
                            previewHtml = '<i class="fas fa-file-excel fa-3x text-success"></i>';
                        } else if (item.mime_type === 'application/vnd.ms-powerpoint' || 
                                   item.mime_type === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
                            // PPT/PPTX preview
                            previewHtml = '<i class="fas fa-file-powerpoint fa-3x text-warning"></i>';
                        } else if (item.mime_type.startsWith('text/')) {
                            // Text file preview
                            previewHtml = '<i class="fas fa-file-alt fa-3x text-secondary"></i>';
                        } else {
                            // Generic file preview
                            previewHtml = '<i class="fas fa-file fa-3x text-secondary"></i>';
                        }
                    } else {
                        // Fallback if MIME type is missing
                        previewHtml = '<i class="fas fa-file fa-3x text-secondary"></i>';
                    }
                    
                    $col.html(`
                        <div class="card border-0 shadow-sm media-item position-relative h-100" data-id="${item.id}" data-url="${item.url || ''}" data-name="${item.name || 'Media item'}" data-mime="${item.mime_type || ''}">
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                ${previewHtml}
                            </div>
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-truncate small" title="${item.name || 'Media item'}">${item.name || 'Media item'}</div>
                                    <a href="${item.url || '#'}" target="_blank" class="btn btn-sm btn-outline-primary preview-file-btn" title="Preview">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="selection-indicator">
                                <i class="fas fa-check"></i>
                            </div>
                            <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-media-btn" data-id="${item.id}" title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `);
                    
                    // Add click event for selection
                    $col.find('.media-item').on('click', function(e) {
                        // Prevent click when clicking on remove button
                        if ($(e.target).hasClass('remove-media-btn') || $(e.target).closest('.remove-media-btn').length > 0) {
                            return;
                        }
                        
                        const id = parseInt($(this).data('id'));
                        const index = selectedMedia.indexOf(id);
                        
                        if (index > -1) {
                            // Already selected, so deselect
                            $(this).removeClass('border-primary');
                            selectedMedia.splice(index, 1);
                        } else {
                            // Not selected, so select
                            if (targetField === 'main_photo') {
                                // For main photo, only allow one selection
                                $('.media-item').removeClass('border-primary');
                                selectedMedia = [id];
                                $(this).addClass('border-primary');
                            } else {
                                // For gallery, allow multiple selections
                                selectedMedia.push(id);
                                $(this).addClass('border-primary');
                            }
                        }
                        
                        // Update select button state
                        $('#select-media-btn').prop('disabled', selectedMedia.length === 0);
                    });
                    
                    // Add click event for remove button
                    $col.find('.remove-media-btn').on('click', function(e) {
                        e.stopPropagation();
                        const mediaId = $(this).data('id');
                        removeMedia(mediaId, $(this).closest('.mb-4'));
                    });
                    
                    // Add click event for preview button
                    $col.find('.preview-file-btn').on('click', function(e) {
                        e.stopPropagation();
                        // The link already has target="_blank" so it will open in a new tab
                        const url = $(this).attr('href');
                        if (url && url !== '#') {
                            window.open(url, '_blank');
                        }
                    });
                    
                    $container.append($col);
                });
            }
            
            // Function to remove media
            function removeMedia(mediaId, $element) {
                if (!confirm('Are you sure you want to delete this media item?')) {
                    return;
                }
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/admin/media/' + mediaId,
                    method: 'DELETE',
                    success: function(data) {
                        if (data.success) {
                            // Remove the element from the DOM
                            $element.fadeOut(300, function() {
                                $(this).remove();
                                
                                // If no media items left, show empty message
                                if ($('#media-library-items .mb-4').length === 0) {
                                    $('#no-media-message').removeClass('d-none');
                                }
                                
                                // Remove from selected media if it was selected
                                const index = selectedMedia.indexOf(mediaId);
                                if (index > -1) {
                                    selectedMedia.splice(index, 1);
                                    $('#select-media-btn').prop('disabled', selectedMedia.length === 0);
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        alert('Error deleting media.');
                    }
                });
            }
            
            // Load more button
            $('#load-more-btn').on('click', function() {
                currentPage++;
                loadMediaLibrary(currentPage);
            });
            
            // Select media button
            $('#select-media-btn').on('click', function() {
                if (targetField === 'main_photo') {
                    // Handle main photo selection
                    if (selectedMedia.length > 0) {
                        const mediaId = selectedMedia[0];
                        $('#main_photo_id').val(mediaId);
                        
                        // Get the media URL from the selected item
                        const $selectedItem = $(`.media-item[data-id="${mediaId}"]`);
                        const mediaUrl = $selectedItem.find('img').attr('src') || '/storage/media/placeholder.jpg';
                        
                        // Update preview with the actual image
                        $('#main-photo-preview').html(`
                            <div class="bg-light d-flex align-items-center justify-content-center mb-2" style="height: 150px;">
                                <img src="${mediaUrl}" class="img-fluid" alt="Selected image 12" style="max-height: 100%; max-width: 100%; object-fit: cover;" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-image fa-2x text-muted\\'></i>'">
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#mediaLibraryModal" data-target="main_photo">
                                <i class="fas fa-folder-open me-1"></i> Change Image
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill ms-2" id="remove-main-photo">
                                <i class="fas fa-trash me-1"></i> Remove
                            </button>
                        `);
                        
                        // Add remove functionality
                        initializeMainPhotoRemove();
                    }
                } else if (targetField === 'gallery') {
                    // Handle gallery photos selection
                    const $galleryPreview = $('#gallery-preview');
                    
                    // If we're adding to existing gallery items, we need to merge them
                    const existingItems = [];
                    $galleryPreview.find('.gallery-item').each(function() {
                        existingItems.push(parseInt($(this).data('id')));
                    });
                    
                    // Add new selected items that aren't already in the gallery
                    const newItems = selectedMedia.filter(id => !existingItems.includes(id));
                    
                    // Update the hidden input with all media IDs
                    const allItems = [...existingItems, ...newItems];
                    $('#product_gallery').val(JSON.stringify(allItems));
                    
                    // Add new items to the gallery preview
                    newItems.forEach(function(mediaId) {
                        // Get the media URL from the selected item
                        const $selectedItem = $(`.media-item[data-id="${mediaId}"]`);
                        const mediaUrl = $selectedItem.find('img').attr('src') || '/storage/media/placeholder.jpg';
                        
                        const $imgContainer = $('<div class="position-relative gallery-item" data-id="' + mediaId + '" draggable="true"></div>');
                        $imgContainer.html(`
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 80px; width: 80px;">
                                <img src="${mediaUrl}" class="img-fluid" alt="Gallery image" style="max-height: 100%; max-width: 100%; object-fit: cover;" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-image text-muted\\'></i>'">
                            </div>
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle p-1 remove-gallery-item" data-id="${mediaId}">
                                <i class="fas fa-times"></i>
                            </button>
                            <input type="hidden" name="product_gallery[]" value="${mediaId}">
                        `);
                        
                        $galleryPreview.append($imgContainer);
                    });
                    
                    // Add drag and drop functionality for gallery reordering
                    initializeGallerySorting();
                    
                    // Add remove functionality to gallery items
                    $('.remove-gallery-item').on('click', function() {
                        const mediaId = parseInt($(this).data('id'));
                        
                        // Remove from the array
                        const index = allItems.indexOf(mediaId);
                        if (index > -1) {
                            allItems.splice(index, 1);
                        }
                        
                        // Update the hidden input
                        $('#product_gallery').val(JSON.stringify(allItems));
                        
                        // Remove the element
                        $(this).closest('.gallery-item').remove();
                    });
                }
                
                // Close the modal
                $('#mediaLibraryModal').modal('hide');
            });
            
            // Function to initialize gallery sorting
            function initializeGallerySorting() {
                const galleryItems = document.querySelectorAll('#gallery-preview .gallery-item');
                
                galleryItems.forEach(item => {
                    item.addEventListener('dragstart', handleDragStart);
                    item.addEventListener('dragover', handleDragOver);
                    item.addEventListener('dragenter', handleDragEnter);
                    item.addEventListener('dragleave', handleDragLeave);
                    item.addEventListener('drop', handleDrop);
                    item.addEventListener('dragend', handleDragEnd);
                });
            }
            
            // Drag and drop variables
            let dragSrcEl = null;
            
            function handleDragStart(e) {
                dragSrcEl = this;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', this.innerHTML);
                this.classList.add('dragging');
            }
            
            function handleDragOver(e) {
                if (e.preventDefault) {
                    e.preventDefault();
                }
                e.dataTransfer.dropEffect = 'move';
                return false;
            }
            
            function handleDragEnter(e) {
                this.classList.add('drag-over');
            }
            
            function handleDragLeave(e) {
                this.classList.remove('drag-over');
            }
            
            function handleDrop(e) {
                if (e.stopPropagation) {
                    e.stopPropagation();
                }
                
                if (dragSrcEl !== this) {
                    // Get the data-id values
                    const srcId = dragSrcEl.getAttribute('data-id');
                    const targetId = this.getAttribute('data-id');
                    
                    // Swap the positions in the DOM
                    const tempSrc = dragSrcEl.innerHTML;
                    dragSrcEl.innerHTML = this.innerHTML;
                    this.innerHTML = tempSrc;
                    
                    // Update the data-id attributes
                    dragSrcEl.setAttribute('data-id', targetId);
                    this.setAttribute('data-id', srcId);
                    
                    // Reattach event listeners to the swapped elements
                    const newSrcItem = dragSrcEl;
                    const newTargetItem = this;
                    
                    // Remove old event listeners
                    newSrcItem.removeEventListener('dragstart', handleDragStart);
                    newSrcItem.removeEventListener('dragover', handleDragOver);
                    newSrcItem.removeEventListener('dragenter', handleDragEnter);
                    newSrcItem.removeEventListener('dragleave', handleDragLeave);
                    newSrcItem.removeEventListener('drop', handleDrop);
                    newSrcItem.removeEventListener('dragend', handleDragEnd);
                    
                    newTargetItem.removeEventListener('dragstart', handleDragStart);
                    newTargetItem.removeEventListener('dragover', handleDragOver);
                    newTargetItem.removeEventListener('dragenter', handleDragEnter);
                    newTargetItem.removeEventListener('dragleave', handleDragLeave);
                    newTargetItem.removeEventListener('drop', handleDrop);
                    newTargetItem.removeEventListener('dragend', handleDragEnd);
                    
                    // Add new event listeners
                    newSrcItem.addEventListener('dragstart', handleDragStart);
                    newSrcItem.addEventListener('dragover', handleDragOver);
                    newSrcItem.addEventListener('dragenter', handleDragEnter);
                    newSrcItem.addEventListener('dragleave', handleDragLeave);
                    newSrcItem.addEventListener('drop', handleDrop);
                    newSrcItem.addEventListener('dragend', handleDragEnd);
                    
                    newTargetItem.addEventListener('dragstart', handleDragStart);
                    newTargetItem.addEventListener('dragover', handleDragOver);
                    newTargetItem.addEventListener('dragenter', handleDragEnter);
                    newTargetItem.addEventListener('dragleave', handleDragLeave);
                    newTargetItem.addEventListener('drop', handleDrop);
                    newTargetItem.addEventListener('dragend', handleDragEnd);
                    
                    // Update the gallery order in the hidden input
                    updateGalleryOrder();
                }
                
                return false;
            }
            
            function handleDragEnd(e) {
                this.classList.remove('dragging');
                document.querySelectorAll('.gallery-item').forEach(item => {
                    item.classList.remove('drag-over');
                });
            }
            
            // Function to update the gallery order in the hidden input
            function updateGalleryOrder() {
                const galleryItems = document.querySelectorAll('#gallery-preview .gallery-item');
                const newOrder = [];
                
                galleryItems.forEach(item => {
                    const mediaId = parseInt(item.getAttribute('data-id'));
                    newOrder.push(mediaId);
                });
                
                // Update the hidden input
                $('#product_gallery').val(JSON.stringify(newOrder));
            }
            
            // Initialize gallery sorting on page load if there are existing items
            if ($('#gallery-preview .gallery-item').length > 0) {
                initializeGallerySorting();
            }
            
            // ========================================
            // DRAG AND DROP FILE UPLOAD FUNCTIONALITY
            // ========================================
            
            // Helper function to handle file upload for product images
            function handleProductImageUpload(file, targetType) {
                // Validate file type - only images for product photos
                const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validImageTypes.includes(file.type)) {
                    alert('Please upload a valid image file (JPEG, PNG, GIF, WEBP).');
                    return;
                }
                
                // Validate file size - 25MB max
                const maxSize = 25 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('File size must be less than 25MB.');
                    return;
                }
                
                const formData = new FormData();
                formData.append('file', file);
                formData.append('name', file.name);
                
                // Show upload indicator
                let $uploadIndicator;
                if (targetType === 'main_photo') {
                    $uploadIndicator = $(`
                        <div class="upload-progress-indicator">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            <span>Uploading...</span>
                        </div>
                    `);
                    $('#main-photo-preview').append($uploadIndicator);
                } else {
                    $uploadIndicator = $(`
                        <div class="position-relative gallery-item uploading-item" style="opacity: 0.6;">
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 80px; width: 80px;">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            </div>
                        </div>
                    `);
                    $('#gallery-preview').append($uploadIndicator);
                }
                
                // Send AJAX request to upload media
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/admin/media',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        $uploadIndicator.remove();
                        
                        if (data.success && data.media) {
                            if (targetType === 'main_photo') {
                                // Set main photo
                                $('#main_photo_id').val(data.media.id);
                                $('#main-photo-preview').html(`
                                    <div class="position-relative">
                                        <img src="${data.media.url}" class="img-fluid mb-2" alt="${data.media.name}" style="max-height: 200px; object-fit: contain;">
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#mediaLibraryModal" data-target="main_photo">
                                            <i class="fas fa-folder-open me-1"></i> Change Image
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill ms-2" id="remove-main-photo">
                                            <i class="fas fa-trash me-1"></i> Remove
                                        </button>
                                    </div>
                                `);
                                initializeMainPhotoRemove();
                            } else {
                                // Add to gallery
                                const $galleryPreview = $('#gallery-preview');
                                let galleryItems = JSON.parse($('#product_gallery').val() || '[]');
                                
                                // Add new media ID to array
                                galleryItems.push(data.media.id);
                                $('#product_gallery').val(JSON.stringify(galleryItems));
                                
                                // Create gallery item element
                                const $imgContainer = $(`
                                    <div class="position-relative gallery-item" data-id="${data.media.id}" draggable="true">
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 80px; width: 80px;">
                                            <img src="${data.media.url}" class="img-fluid" alt="Gallery image" style="max-height: 100%; max-width: 100%; object-fit: cover;" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-image text-muted\\'></i>'">
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle p-1 remove-gallery-item" data-id="${data.media.id}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <input type="hidden" name="product_gallery[]" value="${data.media.id}">
                                    </div>
                                `);
                                
                                $galleryPreview.append($imgContainer);
                                
                                // Reinitialize gallery sorting
                                initializeGallerySorting();
                            }
                        } else {
                            alert('Error uploading file. Please try again.');
                        }
                    },
                    error: function(xhr) {
                        $uploadIndicator.remove();
                        let errorMsg = 'Error uploading file. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        alert(errorMsg);
                    }
                });
            }
            
            // Prevent default drag behaviors on the whole document
            $(document).on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
            
            // Main Photo Upload Area - Drag and Drop
            function initializeMainPhotoDragDrop() {
                // Store reference for use in initializeMainPhotoRemove
                initializeMainPhotoDragDropRef = initializeMainPhotoDragDrop;
                const $mainPhotoArea = $('#main-photo-upload-area, #main-photo-preview');
                
                $mainPhotoArea.off('dragover dragenter dragleave drop');
                
                $mainPhotoArea.on('dragover dragenter', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).addClass('drag-highlight');
                });
                
                $mainPhotoArea.on('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).removeClass('drag-highlight');
                });
                
                $mainPhotoArea.on('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).removeClass('drag-highlight');
                    
                    const files = e.originalEvent.dataTransfer.files;
                    if (files.length > 0) {
                        // Only upload the first file for main photo
                        handleProductImageUpload(files[0], 'main_photo');
                    }
                });
                
                // Click to upload for main photo area (only on upload area, not on existing image)
                $('#main-photo-upload-area').off('click').on('click', function(e) {
                    // Don't trigger if clicking on buttons inside
                    if ($(e.target).is('button') || $(e.target).closest('button').length) {
                        return;
                    }
                    
                    const fileInput = $('<input type="file" accept="image/*" style="display: none;">');
                    $('body').append(fileInput);
                    
                    fileInput.on('change', function() {
                        if (this.files.length > 0) {
                            handleProductImageUpload(this.files[0], 'main_photo');
                        }
                        fileInput.remove();
                    });
                    
                    fileInput.click();
                });
            }
            
            // Gallery Upload Area - Drag and Drop
            function initializeGalleryDragDrop() {
                const $galleryArea = $('#gallery-upload-area');
                
                $galleryArea.off('dragover dragenter dragleave drop click');
                
                $galleryArea.on('dragover dragenter', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).addClass('drag-highlight');
                });
                
                $galleryArea.on('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).removeClass('drag-highlight');
                });
                
                $galleryArea.on('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).removeClass('drag-highlight');
                    
                    const files = e.originalEvent.dataTransfer.files;
                    if (files.length > 0) {
                        // Upload all dropped files to gallery
                        for (let i = 0; i < files.length; i++) {
                            handleProductImageUpload(files[i], 'gallery');
                        }
                    }
                });
                
                // Click to upload for gallery area
                $galleryArea.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const fileInput = $('<input type="file" accept="image/*" multiple style="display: none;">');
                    $('body').append(fileInput);
                    
                    fileInput.on('change', function() {
                        if (this.files.length > 0) {
                            for (let i = 0; i < this.files.length; i++) {
                                handleProductImageUpload(this.files[i], 'gallery');
                            }
                        }
                        fileInput.remove();
                    });
                    
                    fileInput.click();
                });
            }
            
            // Initialize drag and drop on page load
            initializeMainPhotoDragDrop();
            initializeGalleryDragDrop();
            
            // Re-initialize when DOM changes (e.g., after removing main photo)
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        // Check if main photo area needs re-initialization
                        if ($('#main-photo-upload-area').length) {
                            initializeMainPhotoDragDrop();
                        }
                    }
                });
            });
            
            // Observe main photo preview for changes
            const mainPhotoPreview = document.getElementById('main-photo-preview');
            if (mainPhotoPreview) {
                observer.observe(mainPhotoPreview, { childList: true, subtree: true });
            }
            
            // ========================================
            // END DRAG AND DROP FILE UPLOAD
            // ========================================
            
            // Add event handler for existing gallery item remove buttons
            // This fixes the issue where remove buttons for existing gallery items don't work
            $(document).on('click', '.remove-gallery-item', function() {
                const mediaId = parseInt($(this).data('id'));
                
                // Get current gallery items
                let galleryItems = JSON.parse($('#product_gallery').val() || '[]');
                
                // Remove the item from the array
                const index = galleryItems.indexOf(mediaId);
                if (index > -1) {
                    galleryItems.splice(index, 1);
                }
                
                // Update the hidden input
                $('#product_gallery').val(JSON.stringify(galleryItems));
                
                // Remove the element
                $(this).closest('.gallery-item').remove();
            });
            
            // Validate selling price <= MRP
            $('#selling_price').on('input', function() {
                const mrp = parseFloat($('#mrp').val()) || 0;
                const sellingPrice = parseFloat($(this).val()) || 0;
                
                if (mrp > 0 && sellingPrice > mrp) {
                    this.setCustomValidity('Selling price must be less than or equal to MRP.');
                } else {
                    this.setCustomValidity('');
                }
            });
            
            $('#mrp').on('input', function() {
                const mrp = parseFloat($(this).val()) || 0;
                const sellingPrice = parseFloat($('#selling_price').val()) || 0;
                
                if (mrp > 0 && sellingPrice > mrp) {
                    $('#selling_price')[0].setCustomValidity('Selling price must be less than or equal to MRP.');
                } else {
                    $('#selling_price')[0].setCustomValidity('');
                }
            });
        });
    });
})();