@if($subCategories->count() > 0)
<div class="row">
    @foreach($subCategories as $index => $subCategory)
    <div class="col-md-6 mb-3">
        <div class="card h-100 shadow-sm border-0 subcategory-card" 
             style="cursor: pointer; border-radius: 12px; overflow: hidden;">
            <div class="position-relative subcategory-image-wrapper" style="overflow: hidden;">
                @if($subCategory->image)
                    <img src="{{ $subCategory->image_url }}" 
                         class="card-img-top subcategory-image" 
                         alt="{{ $subCategory->name }}" 
                         style="height: 120px; object-fit: cover;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center subcategory-placeholder" 
                         style="height: 120px;">
                        <i class="fas fa-image fa-2x text-muted"></i>
                    </div>
                @endif
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge text-white subcategory-badge" 
                          style="background-color: var(--primary-color);">
                        {{ $subCategory->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <!-- Hover Overlay -->
                <div class="subcategory-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                     style="background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.85), rgba(var(--secondary-rgb), 0.85)); opacity: 0;">
                    <span class="text-white fw-bold">
                        <i class="fas fa-folder-open me-2"></i>View Category
                    </span>
                </div>
            </div>
            <div class="card-body">
                <h6 class="card-title mb-1 subcategory-title">
                    {{ $subCategory->name }}
                </h6>
                <p class="card-text small text-muted mb-0 subcategory-desc">
                    {{ Str::limit($subCategory->description ?? 'No description available', 80) }}
                </p>
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
    /* Subcategory Card Hover Styles */
    .subcategory-card:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
    }
    
    .subcategory-card:hover .subcategory-image {
    }
    
    .subcategory-card:hover .subcategory-placeholder {
        background-color: rgba(var(--primary-rgb), 0.1) !important;
    }
    
    .subcategory-card:hover .subcategory-placeholder i {
        color: var(--primary-color) !important;
    }
    
    .subcategory-card:hover .subcategory-badge {
        box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.4);
    }
    
    .subcategory-card:hover .subcategory-overlay {
        opacity: 1;
    }
    
    .subcategory-card:hover .subcategory-overlay span {
    }
    
    .subcategory-card:hover .subcategory-title {
        color: var(--primary-color);
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .subcategory-card:hover {
        }
    }
</style>
@else
<div class="alert alert-info text-center">
    <i class="fas fa-info-circle me-2"></i>
    No subcategories available for this category.
</div>
@endif