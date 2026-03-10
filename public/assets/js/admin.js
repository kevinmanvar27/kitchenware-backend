
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
            
            // ========================================
            // MEDIA LIBRARY REMOVED
            // Media library functionality has been removed.
            // Direct file uploads are now handled in individual views.
            // ========================================
            
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
