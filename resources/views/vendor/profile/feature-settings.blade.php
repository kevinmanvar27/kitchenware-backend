@extends('vendor.layouts.app')

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
        gap: 1rem;
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
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }
    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }
    @media (max-width: 768px) {
        .feature-item {
            flex-direction: column;
            align-items: flex-start;
        }
        .feature-controls {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Feature Settings'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <div>
                                <h2 class="card-title mb-1 fw-semibold h5">Feature Settings</h2>
                                <p class="text-muted mb-0 small">Enable or disable optional features for your store</p>
                            </div>
                            <a href="{{ route('vendor.profile.store') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                <i class="fas fa-arrow-left me-1"></i> Back to Store Settings
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

                        @if($groupedFeatures->count() > 0)
                            <!-- Info Alert -->
                            <div class="alert alert-info rounded-3 mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-info-circle me-2 mt-1"></i>
                                    <div>
                                        <strong>Optional Features</strong>
                                        <p class="mb-0 mt-1 small">These features have been made available by the administrator for you to enable or disable based on your needs. Toggle them on to add functionality to your store.</p>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('vendor.feature-settings.update') }}" method="POST" id="featureSettingsForm">
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
                                            <div class="feature-item" data-feature="{{ $feature['feature_key'] }}">
                                                <div class="feature-info">
                                                    <div class="feature-name">{{ $feature['feature_name'] }}</div>
                                                    <div class="feature-description">{{ $feature['feature_description'] }}</div>
                                                </div>
                                                <div class="feature-controls">
                                                    <!-- Enable Toggle -->
                                                    <div class="form-check form-switch mb-0">
                                                        <!-- Hidden input to capture unchecked state -->
                                                        <input type="hidden" name="features[{{ $feature['feature_key'] }}]" value="0">
                                                        <input class="form-check-input feature-toggle" 
                                                               type="checkbox" 
                                                               id="feature_{{ $feature['feature_key'] }}"
                                                               name="features[{{ $feature['feature_key'] }}]"
                                                               value="1"
                                                               {{ $feature['vendor_enabled'] ? 'checked' : '' }}
                                                               data-feature="{{ $feature['feature_key'] }}">
                                                        <label class="form-check-label" for="feature_{{ $feature['feature_key'] }}"></label>
                                                    </div>
                                                    
                                                    <!-- Status Badge -->
                                                    <div class="status-container">
                                                        @if($feature['vendor_enabled'])
                                                            <span class="badge bg-success status-badge">
                                                                <i class="fas fa-check-circle me-1"></i>Enabled
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary status-badge">
                                                                <i class="fas fa-times-circle me-1"></i>Disabled
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
                        @else
                            <!-- Empty State -->
                            <div class="empty-state">
                                <i class="fas fa-toggle-on"></i>
                                <h5 class="text-muted">No Optional Features Available</h5>
                                <p class="text-muted mb-0">All features are currently enabled by default or no optional features have been configured by the administrator.</p>
                            </div>
                        @endif
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
    // Handle toggle changes to update status badge
    document.querySelectorAll('.feature-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const featureItem = this.closest('.feature-item');
            const statusContainer = featureItem.querySelector('.status-container');
            
            if (this.checked) {
                statusContainer.innerHTML = '<span class="badge bg-success status-badge"><i class="fas fa-check-circle me-1"></i>Enabled</span>';
            } else {
                statusContainer.innerHTML = '<span class="badge bg-secondary status-badge"><i class="fas fa-times-circle me-1"></i>Disabled</span>';
            }
        });
    });
});
</script>
@endsection
