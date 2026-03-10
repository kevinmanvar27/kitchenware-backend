@extends('admin.layouts.app')

@section('title', 'Feature Settings - ' . config('app.name', 'Laravel'))

@section('styles')
<style>
    .feature-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .feature-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .feature-group-header {
        background: linear-gradient(135deg, var(--theme-color, #6f42c1) 0%, #8b5cf6 100%);
        color: white;
        padding: 1rem 1.25rem;
        border-radius: 10px;
        margin-bottom: 1rem;
    }
    .feature-item {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f3f4;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .feature-item:last-child {
        border-bottom: none;
    }
    .feature-info {
        flex: 1;
        min-width: 200px;
    }
    .feature-name {
        font-weight: 600;
        color: #212529;
        margin-bottom: 0.25rem;
    }
    .feature-description {
        font-size: 0.875rem;
        color: #6c757d;
    }
    .feature-controls {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    .feature-toggle {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }
    .feature-toggle-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-align: center;
    }
    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;
    }
    .form-switch .form-check-input:checked {
        background-color: var(--theme-color, #6f42c1);
        border-color: var(--theme-color, #6f42c1);
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }
    .override-disabled {
        opacity: 0.5;
        pointer-events: none;
    }
    @media (max-width: 768px) {
        .feature-item {
            flex-direction: column;
            align-items: flex-start;
        }
        .feature-controls {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Feature Settings'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <div>
                                <h2 class="card-title mb-1 fw-semibold h5">Feature Management</h2>
                                <p class="text-muted mb-0 small">Control which features are available to vendors</p>
                            </div>
                            <a href="{{ route('admin.settings') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                <i class="fas fa-arrow-left me-1"></i> Back to Settings
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Legend -->
                        <div class="alert alert-info rounded-3 mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle me-2 mt-1"></i>
                                <div>
                                    <strong>How it works:</strong>
                                    <ul class="mb-0 mt-2 ps-3">
                                        <li><strong>Global Enable:</strong> When ON, the feature is available to all vendors.</li>
                                        <li><strong>Allow Vendor Override:</strong> When Global is OFF but Override is ON, vendors can enable the feature for themselves in their settings.</li>
                                        <li>When both are OFF, the feature is completely disabled for all vendors.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.feature-settings.update') }}" method="POST" id="featureSettingsForm">
                            @csrf
                            
                            @foreach($groupedFeatures as $groupKey => $features)
                                <div class="feature-card mb-4">
                                    <div class="feature-group-header">
                                        <h5 class="mb-0 fw-semibold">
                                            <i class="fas fa-{{ $groupKey === 'catalog' ? 'box' : ($groupKey === 'sales' ? 'file-invoice' : ($groupKey === 'customers' ? 'users' : ($groupKey === 'team' ? 'user-friends' : ($groupKey === 'marketing' ? 'bullhorn' : ($groupKey === 'content' ? 'images' : 'cog'))))) }} me-2"></i>
                                            {{ $groupNames[$groupKey] ?? ucfirst($groupKey) }}
                                        </h5>
                                    </div>
                                    
                                    @foreach($features as $feature)
                                        <div class="feature-item" data-feature="{{ $feature->feature_key }}">
                                            <div class="feature-info">
                                                <div class="feature-name">{{ $feature->feature_name }}</div>
                                                <div class="feature-description">{{ $feature->feature_description }}</div>
                                            </div>
                                            <div class="feature-controls">
                                                <!-- Global Enable Toggle -->
                                                <div class="feature-toggle">
                                                    <div class="form-check form-switch mb-0">
                                                        <!-- Hidden input to capture unchecked state -->
                                                        <input type="hidden" name="features[{{ $feature->feature_key }}][is_enabled]" value="0">
                                                        <input class="form-check-input feature-enabled-toggle" 
                                                               type="checkbox" 
                                                               id="enabled_{{ $feature->feature_key }}"
                                                               name="features[{{ $feature->feature_key }}][is_enabled]"
                                                               value="1"
                                                               {{ $feature->is_enabled ? 'checked' : '' }}
                                                               data-feature="{{ $feature->feature_key }}">
                                                    </div>
                                                    <span class="feature-toggle-label">Global Enable</span>
                                                </div>
                                                
                                                <!-- Allow Vendor Override Toggle -->
                                                <div class="feature-toggle {{ $feature->is_enabled ? 'override-disabled' : '' }}" id="override_container_{{ $feature->feature_key }}">
                                                    <div class="form-check form-switch mb-0">
                                                        <!-- Hidden input to capture unchecked state -->
                                                        <input type="hidden" name="features[{{ $feature->feature_key }}][allow_vendor_override]" value="0">
                                                        <input class="form-check-input feature-override-toggle" 
                                                               type="checkbox" 
                                                               id="override_{{ $feature->feature_key }}"
                                                               name="features[{{ $feature->feature_key }}][allow_vendor_override]"
                                                               value="1"
                                                               {{ $feature->allow_vendor_override ? 'checked' : '' }}
                                                               {{ $feature->is_enabled ? 'disabled' : '' }}
                                                               data-feature="{{ $feature->feature_key }}">
                                                    </div>
                                                    <span class="feature-toggle-label">Allow Vendor Override</span>
                                                </div>
                                                
                                                <!-- Status Badge -->
                                                <div>
                                                    @if($feature->is_enabled)
                                                        <span class="badge bg-success status-badge">
                                                            <i class="fas fa-check-circle me-1"></i>Enabled for All
                                                        </span>
                                                    @elseif($feature->allow_vendor_override)
                                                        <span class="badge bg-warning text-dark status-badge">
                                                            <i class="fas fa-user-cog me-1"></i>Vendor Choice
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger status-badge">
                                                            <i class="fas fa-ban me-1"></i>Disabled
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                            
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle global enable toggle
    document.querySelectorAll('.feature-enabled-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const featureKey = this.dataset.feature;
            const overrideContainer = document.getElementById('override_container_' + featureKey);
            const overrideToggle = document.getElementById('override_' + featureKey);
            const featureItem = this.closest('.feature-item');
            
            if (this.checked) {
                // When enabled globally, disable override option visually but keep value submittable
                overrideContainer.classList.add('override-disabled');
                overrideToggle.disabled = true;
            } else {
                // When disabled globally, enable override option
                overrideContainer.classList.remove('override-disabled');
                overrideToggle.disabled = false;
            }
            
            updateStatusBadge(featureItem, this.checked, overrideToggle.checked);
        });
    });
    
    // Handle override toggle
    document.querySelectorAll('.feature-override-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const featureKey = this.dataset.feature;
            const enabledToggle = document.getElementById('enabled_' + featureKey);
            const featureItem = this.closest('.feature-item');
            
            updateStatusBadge(featureItem, enabledToggle.checked, this.checked);
        });
    });
    
    // Before form submit, enable all disabled checkboxes so their values are submitted
    document.getElementById('featureSettingsForm').addEventListener('submit', function(e) {
        // Re-enable disabled override toggles temporarily so they submit their values
        document.querySelectorAll('.feature-override-toggle:disabled').forEach(function(toggle) {
            toggle.disabled = false;
        });
    });
    
    function updateStatusBadge(featureItem, isEnabled, allowOverride) {
        const badgeContainer = featureItem.querySelector('.feature-controls > div:last-child');
        let badgeHtml = '';
        
        if (isEnabled) {
            badgeHtml = '<span class="badge bg-success status-badge"><i class="fas fa-check-circle me-1"></i>Enabled for All</span>';
        } else if (allowOverride) {
            badgeHtml = '<span class="badge bg-warning text-dark status-badge"><i class="fas fa-user-cog me-1"></i>Vendor Choice</span>';
        } else {
            badgeHtml = '<span class="badge bg-danger status-badge"><i class="fas fa-ban me-1"></i>Disabled</span>';
        }
        
        badgeContainer.innerHTML = badgeHtml;
    }
});
</script>
@endsection
