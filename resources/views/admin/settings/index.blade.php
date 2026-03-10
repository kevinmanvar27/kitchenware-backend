@extends('admin.layouts.app')

@section('title', 'Settings - ' . config('app.name', 'Laravel'))

@section('styles')
<!-- Flatpickr CSS for datetime picker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
<style>
    /* Datetime picker styling */
    .datetime-picker {
        background-color: #fff !important;
        cursor: pointer;
    }
    .flatpickr-calendar {
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        border-radius: 8px;
    }
    
    /* Settings Layout - Vertical Tabs for Desktop */
    .settings-container {
        display: flex;
        gap: 1.5rem;
    }
    
    .settings-sidebar {
        flex: 0 0 220px;
        min-width: 220px;
    }
    
    .settings-content {
        flex: 1;
        min-width: 0;
    }
    
    /* Vertical Nav Pills Styling */
    .settings-nav {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 0.75rem;
    }
    
    .settings-nav .nav-link {
        color: #495057;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 0.25rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
        font-weight: 500;
        white-space: nowrap;
    }
    
    .settings-nav .nav-link:hover {
        background: rgba(111, 66, 193, 0.1);
        color: var(--theme-color, #6f42c1);
    }
    
    .settings-nav .nav-link.active {
        background: var(--theme-color, #6f42c1);
        color: #fff;
        box-shadow: 0 2px 8px rgba(111, 66, 193, 0.3);
    }
    
    .settings-nav .nav-link i {
        width: 18px;
        text-align: center;
        font-size: 0.95rem;
    }
    
    /* Mobile Dropdown for Tabs */
    .settings-mobile-dropdown {
        display: none;
    }
    
    .settings-dropdown-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.875rem 1rem;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        font-weight: 500;
        color: #212529;
        transition: all 0.2s ease;
    }
    
    .settings-dropdown-btn:hover,
    .settings-dropdown-btn:focus {
        background: rgba(111, 66, 193, 0.05);
        border-color: var(--theme-color, #6f42c1);
        color: var(--theme-color, #6f42c1);
    }
    
    .settings-dropdown-btn .current-tab {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .settings-dropdown-btn .current-tab i {
        color: var(--theme-color, #6f42c1);
    }
    
    .settings-dropdown-menu {
        width: 100%;
        max-height: 300px;
        overflow-y: auto;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        border: 1px solid #dee2e6;
        padding: 0.5rem;
    }
    
    .settings-dropdown-menu .dropdown-item {
        padding: 0.75rem 1rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 500;
    }
    
    .settings-dropdown-menu .dropdown-item:hover {
        background: rgba(111, 66, 193, 0.1);
        color: var(--theme-color, #6f42c1);
    }
    
    .settings-dropdown-menu .dropdown-item.active {
        background: var(--theme-color, #6f42c1);
        color: #fff;
    }
    
    .settings-dropdown-menu .dropdown-item i {
        width: 18px;
        text-align: center;
    }
    
    /* Responsive Breakpoints */
    @media (max-width: 991.98px) {
        .settings-container {
            flex-direction: column;
        }
        
        .settings-sidebar {
            display: none;
        }
        
        .settings-mobile-dropdown {
            display: block;
            margin-bottom: 1.5rem;
        }
    }
    
    /* Tab Content Styling */
    .settings-tab-content .tab-pane {
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Settings'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                            <h2 class="card-title mb-0 fw-semibold h5 h4-md">Settings</h2>
                            <form action="{{ route('admin.settings.reset') }}" method="POST" id="resetForm">
                                @csrf
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" id="resetButton">
                                    <i class="fas fa-sync-alt me-1"></i><span class="d-none d-sm-inline">Reset to Default</span><span class="d-sm-none">Reset</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Success!</strong> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Error!</strong> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                            @csrf
                            @method('POST')
                            <input type="hidden" name="active_tab" id="activeTabInput" value="general">
                            
                            <div class="settings-container">
                                <!-- Mobile Dropdown Navigation -->
                                <div class="settings-mobile-dropdown dropdown">
                                    <button class="settings-dropdown-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="current-tab">
                                            <i class="fas fa-cog"></i>
                                            <span id="currentTabLabel">General</span>
                                        </span>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <ul class="dropdown-menu settings-dropdown-menu">
                                        <li><a class="dropdown-item active" href="#" data-tab="general-tab"><i class="fas fa-cog"></i> General</a></li>
                                        <li><a class="dropdown-item" href="#" data-tab="social-tab"><i class="fas fa-hashtag"></i> Social Media</a></li>
                                        <li><a class="dropdown-item" href="#" data-tab="appearance-tab"><i class="fas fa-palette"></i> Appearance</a></li>
                                        <li><a class="dropdown-item" href="#" data-tab="site-management-tab"><i class="fas fa-server"></i> Site Management</a></li>
                                        <li><a class="dropdown-item" href="#" data-tab="payment-tab"><i class="fas fa-credit-card"></i> Payment</a></li>
                                        <li><a class="dropdown-item" href="#" data-tab="notifications-tab"><i class="fas fa-bell"></i> Notifications</a></li>
                                        <li><a class="dropdown-item" href="#" data-tab="application-tab"><i class="fas fa-mobile-alt"></i> Application Links</a></li>
                                        <li><a class="dropdown-item" href="#" data-tab="database-tab"><i class="fas fa-database"></i> Database Management</a></li>
                                        <li><a class="dropdown-item" href="#" data-tab="frontend-tab"><i class="fas fa-window-maximize"></i> Frontend Settings</a></li>
                                        <li><a class="dropdown-item" href="#" data-tab="subscription-tab"><i class="fas fa-crown"></i> Subscription Plans</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.feature-settings.index') }}"><i class="fas fa-toggle-on"></i> Feature Control</a></li>
                                    </ul>
                                </div>
                                
                                <!-- Desktop Sidebar Navigation -->
                                <div class="settings-sidebar">
                                    <nav class="settings-nav nav flex-column" id="settingsTabs" role="tablist">
                                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                            <i class="fas fa-cog"></i> General
                                        </button>
                                        <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
                                            <i class="fas fa-hashtag"></i> Social Media
                                        </button>
                                        <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance" type="button" role="tab">
                                            <i class="fas fa-palette"></i> Appearance
                                        </button>
                                        <button class="nav-link" id="site-management-tab" data-bs-toggle="tab" data-bs-target="#site-management" type="button" role="tab">
                                            <i class="fas fa-server"></i> Site Management
                                        </button>
                                        <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab">
                                            <i class="fas fa-credit-card"></i> Payment
                                        </button>
                                        <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                                            <i class="fas fa-bell"></i> Notifications
                                        </button>
                                        <button class="nav-link" id="application-tab" data-bs-toggle="tab" data-bs-target="#application" type="button" role="tab">
                                            <i class="fas fa-mobile-alt"></i> App Links
                                        </button>
                                        <button class="nav-link" id="database-tab" data-bs-toggle="tab" data-bs-target="#database" type="button" role="tab">
                                            <i class="fas fa-database"></i> Database
                                        </button>
                                        <button class="nav-link" id="frontend-tab" data-bs-toggle="tab" data-bs-target="#frontend" type="button" role="tab">
                                            <i class="fas fa-window-maximize"></i> Frontend
                                        </button>
                                        <button class="nav-link" id="subscription-tab" data-bs-toggle="tab" data-bs-target="#subscription" type="button" role="tab">
                                            <i class="fas fa-crown"></i> Subscriptions
                                        </button>
                                        <a href="{{ route('admin.feature-settings.index') }}" class="nav-link">
                                            <i class="fas fa-toggle-on"></i> Feature Control
                                        </a>
                                    </nav>
                                </div>
                                
                                <!-- Tab Content -->
                                <div class="settings-content">
                                    <div class="tab-content settings-tab-content" id="settingsTabsContent">
                                <!-- General Settings Tab -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row g-4">
                                        <!-- Site Title -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Site Title</label>
                                            <input type="text" name="site_title" value="{{ old('site_title', $setting->site_title) }}" class="form-control" placeholder="Enter site title">
                                        </div>
                                        
                                        <!-- Site Description -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Site Description</label>
                                            <textarea name="site_description" rows="3" class="form-control" placeholder="Enter site description">{{ old('site_description', $setting->site_description) }}</textarea>
                                        </div>
                                        
                                        <!-- Tagline -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Tagline</label>
                                            <input type="text" name="tagline" value="{{ old('tagline', $setting->tagline) }}" class="form-control" placeholder="Enter tagline">
                                            <div class="form-text">A short, memorable phrase that captures your brand essence</div>
                                        </div>
                                        
                                        <!-- Header Logo -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Header Logo</label>
                                            <input type="file" name="header_logo" class="form-control">
                                            @if($setting->header_logo)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $setting->header_logo) }}" alt="Header Logo" class="img-fluid rounded" style="max-height: 80px;">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_header_logo" id="removeHeaderLogo">
                                                        <label class="form-check-label" for="removeHeaderLogo">
                                                            Remove header logo
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Footer Logo -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Footer Logo</label>
                                            <input type="file" name="footer_logo" class="form-control">
                                            @if($setting->footer_logo)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $setting->footer_logo) }}" alt="Footer Logo" class="img-fluid rounded" style="max-height: 80px;">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_footer_logo" id="removeFooterLogo">
                                                        <label class="form-check-label" for="removeFooterLogo">
                                                            Remove footer logo
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Favicon -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Favicon</label>
                                            <input type="file" name="favicon" class="form-control">
                                            @if($setting->favicon)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $setting->favicon) }}" alt="Favicon" class="img-fluid rounded" style="max-height: 50px;">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_favicon" id="removeFavicon">
                                                        <label class="form-check-label" for="removeFavicon">
                                                            Remove favicon
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Footer Text -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Footer Text</label>
                                            <textarea name="footer_text" rows="3" class="form-control" placeholder="Enter footer text">{{ old('footer_text', $setting->footer_text) }}</textarea>
                                        </div>
                                        
                                        <!-- Address -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Address</label>
                                            <textarea name="address" rows="3" class="form-control" placeholder="Enter company address">{{ old('address', $setting->address) }}</textarea>
                                        </div>
                                        
                                        <!-- GST Number -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">GST Number</label>
                                            <input type="text" name="gst_number" value="{{ old('gst_number', $setting->gst_number) }}" class="form-control" placeholder="Enter GST number">
                                        </div>
                                        
                                        <!-- Authorized Signatory -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Authorized Signatory</label>
                                            <input type="file" name="authorized_signatory" class="form-control">
                                            @if($setting->authorized_signatory)
                                                <div class="mt-2">
                                                    <a href="{{ asset('storage/' . $setting->authorized_signatory) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-file-download me-1"></i> View Current Signatory
                                                    </a>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_authorized_signatory" id="removeAuthorizedSignatory">
                                                        <label class="form-check-label" for="removeAuthorizedSignatory">
                                                            Remove authorized signatory
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Company Email -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Company Email</label>
                                            <input type="email" name="company_email" value="{{ old('company_email', $setting->company_email) }}" class="form-control" placeholder="Enter company email">
                                        </div>
                                        
                                        <!-- Company Phone -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Company Phone</label>
                                            <input type="number" name="company_phone" value="{{ old('company_phone', $setting->company_phone) }}" class="form-control" placeholder="Enter company phone">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Social Media Tab -->
                                <div class="tab-pane fade" id="social" role="tabpanel">
                                    <div class="row g-4">
                                        <!-- Facebook URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Facebook URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-facebook-f text-primary"></i>
                                                </span>
                                                <input type="url" name="facebook_url" value="{{ old('facebook_url', $setting->facebook_url) }}" class="form-control" placeholder="https://facebook.com/yourpage">
                                            </div>
                                        </div>
                                        
                                        <!-- Twitter URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Twitter URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-twitter text-info"></i>
                                                </span>
                                                <input type="url" name="twitter_url" value="{{ old('twitter_url', $setting->twitter_url) }}" class="form-control" placeholder="https://twitter.com/yourhandle">
                                            </div>
                                        </div>
                                        
                                        <!-- Instagram URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Instagram URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-instagram text-danger"></i>
                                                </span>
                                                <input type="url" name="instagram_url" value="{{ old('instagram_url', $setting->instagram_url) }}" class="form-control" placeholder="https://instagram.com/yourhandle">
                                            </div>
                                        </div>
                                        
                                        <!-- LinkedIn URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">LinkedIn URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-linkedin-in text-primary"></i>
                                                </span>
                                                <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $setting->linkedin_url) }}" class="form-control" placeholder="https://linkedin.com/company/yourcompany">
                                            </div>
                                        </div>
                                        
                                        <!-- YouTube URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">YouTube URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-youtube text-danger"></i>
                                                </span>
                                                <input type="url" name="youtube_url" value="{{ old('youtube_url', $setting->youtube_url) }}" class="form-control" placeholder="https://youtube.com/yourchannel">
                                            </div>
                                        </div>
                                        
                                        <!-- WhatsApp URL -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">WhatsApp URL</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-surface border-default">
                                                    <i class="fab fa-whatsapp text-success"></i>
                                                </span>
                                                <input type="url" name="whatsapp_url" value="{{ old('whatsapp_url', $setting->whatsapp_url) }}" class="form-control" placeholder="https://wa.me/yourphonenumber">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Appearance Tab -->
                                <div class="tab-pane fade" id="appearance" role="tabpanel">
                                    <div class="row g-4">
                                        <!-- Theme Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Primary Theme Color</label>
                                            <input type="color" name="theme_color" value="{{ old('theme_color', $setting->theme_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for primary buttons and links.</div>
                                        </div>
                                        
                                        <!-- Background Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Background Color</label>
                                            <input type="color" name="background_color" value="{{ old('background_color', $setting->background_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for the main background.</div>
                                        </div>
                                        
                                        <!-- Font Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Font Color</label>
                                            <input type="color" name="font_color" value="{{ old('font_color', $setting->font_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for the main text.</div>
                                        </div>
                                        
                                        <!-- Sidebar Text Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Sidebar Text Color</label>
                                            <input type="color" name="sidebar_text_color" value="{{ old('sidebar_text_color', $setting->sidebar_text_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for sidebar text.</div>
                                        </div>
                                        
                                        <!-- Heading Text Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Heading Text Color</label>
                                            <input type="color" name="heading_text_color" value="{{ old('heading_text_color', $setting->heading_text_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for headings (H1, H2, etc.).</div>
                                        </div>
                                        
                                        <!-- Label Text Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Label Text Color</label>
                                            <input type="color" name="label_text_color" value="{{ old('label_text_color', $setting->label_text_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for form labels.</div>
                                        </div>
                                        
                                        <!-- General Text Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">General Text Color</label>
                                            <input type="color" name="general_text_color" value="{{ old('general_text_color', $setting->general_text_color) }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for general body text.</div>
                                        </div>
                                        
                                        <!-- Link Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Link Color</label>
                                            <input type="color" name="link_color" value="{{ old('link_color', $setting->link_color ?? '#333333') }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for links.</div>
                                        </div>
                                        
                                        <!-- Link Hover Color -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Link Hover Color</label>
                                            <input type="color" name="link_hover_color" value="{{ old('link_hover_color', $setting->link_hover_color ?? '#FF6B00') }}" class="form-control form-control-color">
                                            <div class="form-text">This color will be used for links on hover.</div>
                                        </div>
                                        
                                        <!-- Font Style -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Font Style</label>
                                            <select class="form-select" name="font_style">
                                                <option value="Arial, sans-serif" {{ old('font_style', $setting->font_style ?? 'Arial, sans-serif') == 'Arial, sans-serif' ? 'selected' : '' }}>Arial</option>
                                                <option value="'Times New Roman', serif" {{ old('font_style', $setting->font_style ?? 'Arial, sans-serif') == "'Times New Roman', serif" ? 'selected' : '' }}>Times New Roman</option>
                                                <option value="'Courier New', monospace" {{ old('font_style', $setting->font_style ?? 'Arial, sans-serif') == "'Courier New', monospace" ? 'selected' : '' }}>Courier New</option>
                                                <option value="Georgia, serif" {{ old('font_style', $setting->font_style ?? 'Arial, sans-serif') == 'Georgia, serif' ? 'selected' : '' }}>Georgia</option>
                                                <option value="Verdana, sans-serif" {{ old('font_style', $setting->font_style ?? 'Arial, sans-serif') == 'Verdana, sans-serif' ? 'selected' : '' }}>Verdana</option>
                                            </select>
                                            <div class="form-text">Select the default font style for the website.</div>
                                        </div>
                                        
                                        <!-- Responsive Font Size Matrix -->
                                        <div class="col-12">
                                            <hr class="my-4">
                                            <h4 class="mb-4">Responsive Font Size Matrix</h4>
                                            
                                            @php
                                                $fontOptions = [
                                                    'Arial, sans-serif' => 'Arial',
                                                    'Times New Roman, serif' => 'Times New Roman',
                                                    'Courier New, monospace' => 'Courier New',
                                                    'Georgia, serif' => 'Georgia',
                                                    'Verdana, sans-serif' => 'Verdana',
                                                    'Roboto, sans-serif' => 'Roboto',
                                                    'Open Sans, sans-serif' => 'Open Sans',
                                                    'Lato, sans-serif' => 'Lato',
                                                    'Poppins, sans-serif' => 'Poppins',
                                                    'Montserrat, sans-serif' => 'Montserrat',
                                                ];
                                                $defaultFont = 'Arial, sans-serif';
                                            @endphp
                                            
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Element</th>
                                                            <th>Font Type</th>
                                                            <th>Desktop (px)</th>
                                                            <th>Tablet (px)</th>
                                                            <th>Mobile (px)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>H1</td>
                                                            <td>
                                                                <select name="h1_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h1_font_family', $setting->h1_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h1_size" value="{{ old('desktop_h1_size', $setting->desktop_h1_size ?? 36) }}" class="form-control form-control-sm" min="1" placeholder="36"></td>
                                                            <td><input type="number" name="tablet_h1_size" value="{{ old('tablet_h1_size', $setting->tablet_h1_size ?? 32) }}" class="form-control form-control-sm" min="1" placeholder="32"></td>
                                                            <td><input type="number" name="mobile_h1_size" value="{{ old('mobile_h1_size', $setting->mobile_h1_size ?? 28) }}" class="form-control form-control-sm" min="1" placeholder="28"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>H2</td>
                                                            <td>
                                                                <select name="h2_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h2_font_family', $setting->h2_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h2_size" value="{{ old('desktop_h2_size', $setting->desktop_h2_size ?? 30) }}" class="form-control form-control-sm" min="1" placeholder="30"></td>
                                                            <td><input type="number" name="tablet_h2_size" value="{{ old('tablet_h2_size', $setting->tablet_h2_size ?? 28) }}" class="form-control form-control-sm" min="1" placeholder="28"></td>
                                                            <td><input type="number" name="mobile_h2_size" value="{{ old('mobile_h2_size', $setting->mobile_h2_size ?? 24) }}" class="form-control form-control-sm" min="1" placeholder="24"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>H3</td>
                                                            <td>
                                                                <select name="h3_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h3_font_family', $setting->h3_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h3_size" value="{{ old('desktop_h3_size', $setting->desktop_h3_size ?? 24) }}" class="form-control form-control-sm" min="1" placeholder="24"></td>
                                                            <td><input type="number" name="tablet_h3_size" value="{{ old('tablet_h3_size', $setting->tablet_h3_size ?? 22) }}" class="form-control form-control-sm" min="1" placeholder="22"></td>
                                                            <td><input type="number" name="mobile_h3_size" value="{{ old('mobile_h3_size', $setting->mobile_h3_size ?? 20) }}" class="form-control form-control-sm" min="1" placeholder="20"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>H4</td>
                                                            <td>
                                                                <select name="h4_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h4_font_family', $setting->h4_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h4_size" value="{{ old('desktop_h4_size', $setting->desktop_h4_size ?? 20) }}" class="form-control form-control-sm" min="1" placeholder="20"></td>
                                                            <td><input type="number" name="tablet_h4_size" value="{{ old('tablet_h4_size', $setting->tablet_h4_size ?? 18) }}" class="form-control form-control-sm" min="1" placeholder="18"></td>
                                                            <td><input type="number" name="mobile_h4_size" value="{{ old('mobile_h4_size', $setting->mobile_h4_size ?? 16) }}" class="form-control form-control-sm" min="1" placeholder="16"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>H5</td>
                                                            <td>
                                                                <select name="h5_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h5_font_family', $setting->h5_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h5_size" value="{{ old('desktop_h5_size', $setting->desktop_h5_size ?? 18) }}" class="form-control form-control-sm" min="1" placeholder="18"></td>
                                                            <td><input type="number" name="tablet_h5_size" value="{{ old('tablet_h5_size', $setting->tablet_h5_size ?? 16) }}" class="form-control form-control-sm" min="1" placeholder="16"></td>
                                                            <td><input type="number" name="mobile_h5_size" value="{{ old('mobile_h5_size', $setting->mobile_h5_size ?? 14) }}" class="form-control form-control-sm" min="1" placeholder="14"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>H6</td>
                                                            <td>
                                                                <select name="h6_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('h6_font_family', $setting->h6_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_h6_size" value="{{ old('desktop_h6_size', $setting->desktop_h6_size ?? 16) }}" class="form-control form-control-sm" min="1" placeholder="16"></td>
                                                            <td><input type="number" name="tablet_h6_size" value="{{ old('tablet_h6_size', $setting->tablet_h6_size ?? 14) }}" class="form-control form-control-sm" min="1" placeholder="14"></td>
                                                            <td><input type="number" name="mobile_h6_size" value="{{ old('mobile_h6_size', $setting->mobile_h6_size ?? 12) }}" class="form-control form-control-sm" min="1" placeholder="12"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Body Text</td>
                                                            <td>
                                                                <select name="body_font_family" class="form-select form-select-sm">
                                                                    @foreach($fontOptions as $value => $label)
                                                                        <option value="{{ $value }}" {{ old('body_font_family', $setting->body_font_family ?? $defaultFont) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="desktop_body_size" value="{{ old('desktop_body_size', $setting->desktop_body_size ?? 16) }}" class="form-control form-control-sm" min="1" placeholder="16"></td>
                                                            <td><input type="number" name="tablet_body_size" value="{{ old('tablet_body_size', $setting->tablet_body_size ?? 14) }}" class="form-control form-control-sm" min="1" placeholder="14"></td>
                                                            <td><input type="number" name="mobile_body_size" value="{{ old('mobile_body_size', $setting->mobile_body_size ?? 12) }}" class="form-control form-control-sm" min="1" placeholder="12"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Site Management Tab -->
                                <div class="tab-pane fade" id="site-management" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Maintenance Mode</h4>
                                            
                                            <div class="row g-4">
                                                <!-- Enable Maintenance Mode -->
                                                <div class="col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenanceMode" value="1" {{ old('maintenance_mode', $setting->maintenance_mode) ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-medium" for="maintenanceMode">Enable Maintenance Mode</label>
                                                        <div class="form-text">When enabled, only admin users can access the site</div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Maintenance End Time -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Maintenance End Time (Optional)</label>
                                                    <input type="text" name="maintenance_end_time" id="maintenanceEndTime" value="{{ old('maintenance_end_time', $setting->maintenance_end_time ? \Carbon\Carbon::parse($setting->maintenance_end_time)->format('d/m/Y H:i') : '') }}" class="form-control datetime-picker" placeholder="dd/mm/yyyy HH:mm">
                                                    <div class="form-text">Maintenance mode will auto-disable at this time</div>
                                                </div>
                                                
                                                <!-- Maintenance Message -->
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">Maintenance Message</label>
                                                    <textarea name="maintenance_message" rows="3" class="form-control" placeholder="We are currently under maintenance. The website will be back online approximately at {end_time}.">{{ old('maintenance_message', $setting->maintenance_message ?? 'We are currently under maintenance. The website will be back online approximately at {end_time}.') }}</textarea>
                                                    <div class="form-text">Use {end_time} placeholder to show maintenance end time</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <div class="col-12">
                                            <h4 class="mb-4">Coming Soon Mode</h4>
                                            
                                            <div class="row g-4">
                                                <!-- Enable Coming Soon Mode -->
                                                <div class="col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="coming_soon_mode" id="comingSoonMode" value="1" {{ old('coming_soon_mode', $setting->coming_soon_mode) ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-medium" for="comingSoonMode">Enable Coming Soon Mode</label>
                                                        <div class="form-text">When enabled, shows a coming soon page to visitors</div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Launch Time -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Launch Time (Optional)</label>
                                                    <input type="text" name="launch_time" id="launchTime" value="{{ old('launch_time', $setting->launch_time ? \Carbon\Carbon::parse($setting->launch_time)->format('d/m/Y H:i') : '') }}" class="form-control datetime-picker" placeholder="dd/mm/yyyy HH:mm">
                                                    <div class="form-text">Coming soon mode will auto-disable at this time</div>
                                                </div>
                                                
                                                <!-- Coming Soon Message -->
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">Coming Soon Message</label>
                                                    <textarea name="coming_soon_message" rows="3" class="form-control" placeholder="We're launching soon! Our amazing platform will be available at {launch_time}.">{{ old('coming_soon_message', $setting->coming_soon_message ?? "We're launching soon! Our amazing platform will be available at {launch_time}.") }}</textarea>
                                                    <div class="form-text">Use {launch_time} placeholder to show launch time</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Payment Settings Tab -->
                                <div class="tab-pane fade" id="payment" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Payment Settings</h4>
                                            <p class="text-muted">Configure Razorpay integration and payment processing options.</p>
                                            
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Setup Required</strong> - Please ensure you have your Razorpay credentials ready.
                                            </div>
                                            
                                            <div class="card border-0 shadow-sm mb-4">
                                                <div class="card-header bg-light">
                                                    <h5 class="card-title mb-0">Razorpay Configuration</h5>
                                                </div>
                                                <div class="card-body">
                                                    @if(empty($setting->firebase_project_id) || empty($setting->firebase_client_email) || empty($setting->firebase_private_key))
                                                        <div class="alert alert-warning mb-3">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            <strong>Firebase is not fully configured.</strong> Push notifications will not work until you complete the configuration.
                                                            <a href="{{ route('admin.firebase.setup-guide') }}" class="alert-link">View the setup guide</a> for step-by-step instructions.
                                                        </div>
                                                    @endif
                                                    <div class="row g-4">
                                                        <!-- Razorpay Key ID -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">Razorpay Key ID</label>
                                                            <input type="text" name="razorpay_key_id" value="{{ old('razorpay_key_id', $setting->razorpay_key_id) }}" class="form-control" placeholder="rzp_test_...">
                                                            <div class="form-text">Your Razorpay Key ID for test or live mode</div>
                                                        </div>
                                                        
                                                        <!-- Razorpay Key Secret -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">Razorpay Key Secret</label>
                                                            <input type="password" name="razorpay_key_secret" value="{{ old('razorpay_key_secret', $setting->razorpay_key_secret) }}" class="form-control" placeholder="Enter secret key">
                                                            <div class="form-text">Your Razorpay Key Secret (keep this secure)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- RazorpayX Configuration for Vendor Payouts -->
                                            <div class="card border-0 shadow-sm mb-4">
                                                <div class="card-header bg-light">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-money-bill-transfer me-2"></i>RazorpayX Configuration (Vendor Payouts)
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="alert alert-warning mb-4">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        <strong>Important:</strong> RazorpayX is used for vendor payouts. These credentials are different from regular Razorpay payment gateway credentials.
                                                    </div>
                                                    
                                                    <div class="row g-4">
                                                        <!-- RazorpayX Mode -->
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-medium">Mode</label>
                                                            <div class="d-flex gap-4">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="razorpayx_mode" id="razorpayxModeTest" value="test" {{ old('razorpayx_mode', $setting->razorpayx_mode ?? 'test') == 'test' ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="razorpayxModeTest">
                                                                        <span class="badge bg-warning text-dark">Test Mode</span>
                                                                    </label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="razorpayx_mode" id="razorpayxModeLive" value="live" {{ old('razorpayx_mode', $setting->razorpayx_mode ?? 'test') == 'live' ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="razorpayxModeLive">
                                                                        <span class="badge bg-success">Live Mode</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="form-text">Select test mode for development/testing, live mode for production</div>
                                                        </div>
                                                        
                                                        <!-- RazorpayX Key ID -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">RazorpayX Key ID</label>
                                                            <input type="text" name="razorpayx_key_id" value="{{ old('razorpayx_key_id', $setting->razorpayx_key_id) }}" class="form-control" placeholder="rzp_test_...">
                                                            <div class="form-text">Your RazorpayX API Key ID</div>
                                                        </div>
                                                        
                                                        <!-- RazorpayX Key Secret -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">RazorpayX Key Secret</label>
                                                            <input type="password" name="razorpayx_key_secret" value="{{ old('razorpayx_key_secret', $setting->razorpayx_key_secret) }}" class="form-control" placeholder="Enter RazorpayX secret key">
                                                            <div class="form-text">Your RazorpayX API Key Secret (keep this secure)</div>
                                                        </div>
                                                        
                                                        <!-- RazorpayX Account Number -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">RazorpayX Account Number</label>
                                                            <input type="text" name="razorpayx_account_number" value="{{ old('razorpayx_account_number', $setting->razorpayx_account_number) }}" class="form-control" placeholder="2323230000000000">
                                                            <div class="form-text">Your RazorpayX virtual account number for payouts</div>
                                                        </div>
                                                        
                                                        <!-- RazorpayX Webhook Secret -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">RazorpayX Webhook Secret</label>
                                                            <input type="password" name="razorpayx_webhook_secret" value="{{ old('razorpayx_webhook_secret', $setting->razorpayx_webhook_secret) }}" class="form-control" placeholder="Enter webhook secret (optional)">
                                                            <div class="form-text">Webhook secret for verifying RazorpayX callbacks (optional)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Notification Settings Tab -->
                                <div class="tab-pane fade" id="notifications" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Notification Settings</h4>
                                            <p class="text-muted">Configure Firebase Cloud Messaging for push notifications.</p>
                                            
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Setup Required</strong> - Firebase Cloud Messaging Configuration
                                            </div>
                                            
                                            <div class="card border-0 shadow-sm mb-4">
                                                <div class="card-header bg-light">
                                                    <h5 class="card-title mb-0">Firebase Cloud Messaging Configuration</h5>
                                                </div>
                                                <div class="card-body">
                                                    @if(empty($setting->firebase_project_id) || empty($setting->firebase_client_email) || empty($setting->firebase_private_key))
                                                        <div class="alert alert-warning mb-3">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            <strong>Firebase is not fully configured.</strong> Push notifications will not work until you complete the configuration.
                                                            <a href="{{ route('admin.firebase.setup-guide') }}" class="alert-link">View the setup guide</a> for step-by-step instructions.
                                                        </div>
                                                    @endif
                                                    <div class="row g-4">
                                                        <!-- Firebase Project ID -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">Firebase Project ID</label>
                                                            <input type="text" name="firebase_project_id" value="{{ old('firebase_project_id', $setting->firebase_project_id) }}" class="form-control" placeholder="your-project-id">
                                                            <div class="form-text">Project ID from Firebase Console</div>
                                                        </div>
                                                        
                                                        <!-- Firebase Client Email -->
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium">Firebase Client Email</label>
                                                            <input type="email" name="firebase_client_email" value="{{ old('firebase_client_email', $setting->firebase_client_email) }}" class="form-control" placeholder="firebase-adminsdk-xxxxx@your-project.iam.gserviceaccount.com">
                                                            <div class="form-text">Service account email from Firebase</div>
                                                        </div>
                                                        
                                                        <!-- Firebase Private Key -->
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-medium">Firebase Private Key</label>
                                                            <textarea name="firebase_private_key" rows="6" class="form-control font-monospace" placeholder="-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----">{{ old('firebase_private_key', $setting->firebase_private_key) }}</textarea>
                                                            <div class="form-text">Private key from Firebase service account JSON file</div>
                                                        </div>
                                                        
                                                        <!-- Action Buttons -->
                                                        <div class="col-12">
                                                            <div class="d-flex gap-2">
                                                                <button type="button" class="btn btn-outline-primary" id="testFirebaseConfig">
                                                                    <i class="fas fa-vial me-1"></i> Test Configuration
                                                                </button>
                                                                <button type="button" class="btn btn-outline-secondary" id="viewFirebaseStats">
                                                                    <i class="fas fa-chart-bar me-1"></i> View Statistics
                                                                </button>
                                                                <a href="{{ route('admin.firebase.diagnostics') }}" class="btn btn-outline-info">
                                                                    <i class="fas fa-stethoscope me-1"></i> Run Diagnostics
                                                                </a>
                                                                <a href="{{ route('admin.firebase.setup-guide') }}" class="btn btn-outline-success">
                                                                    <i class="fas fa-book me-1"></i> Setup Guide
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Test Results Modal -->
                                            <div class="modal fade" id="firebaseTestModal" tabindex="-1" aria-labelledby="firebaseTestModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="firebaseTestModalLabel">Firebase Configuration Test</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div id="testResults">
                                                                <div class="text-center py-5">
                                                                    <div class="spinner-border text-primary" role="status">
                                                                        <span class="visually-hidden">Loading...</span>
                                                                    </div>
                                                                    <p class="mt-2">Testing configuration...</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Statistics Modal -->
                                            <div class="modal fade" id="firebaseStatsModal" tabindex="-1" aria-labelledby="firebaseStatsModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="firebaseStatsModalLabel">Firebase Notification Statistics</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div id="statsContent">
                                                                <div class="text-center py-5">
                                                                    <div class="spinner-border text-primary" role="status">
                                                                        <span class="visually-hidden">Loading...</span>
                                                                    </div>
                                                                    <p class="mt-2">Loading statistics...</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Password Tab -->
                                <div class="tab-pane fade" id="password" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Change Password</h4>
                                            
                                            <div class="row g-4">
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">Current Password</label>
                                                    <div class="input-group">
                                                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter current password" id="current_password">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('current_password')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">New Password</label>
                                                    <div class="input-group">
                                                        <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Enter new password" id="new_password">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('new_password')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">Confirm New Password</label>
                                                    <div class="input-group">
                                                        <input type="password" name="new_password_confirmation" class="form-control @error('new_password_confirmation') is-invalid @enderror" placeholder="Confirm new password" id="new_password_confirmation">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password_confirmation">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('new_password_confirmation')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="col-12">
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        Password must be at least 8 characters long.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Application Links Tab -->
                                <div class="tab-pane fade" id="application" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Application Store Links</h4>
                                            <p class="text-muted">Configure links to your mobile applications in app stores.</p>
                                            
                                            <div class="row g-4">
                                                <!-- App Store Link -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">App Store Link</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-surface border-default">
                                                            <i class="fab fa-apple text-dark"></i>
                                                        </span>
                                                        <input type="url" name="app_store_link" value="{{ old('app_store_link', $setting->app_store_link) }}" class="form-control" placeholder="https://apps.apple.com/app/...">
                                                    </div>
                                                    <div class="form-text">Link to your application in the Apple App Store</div>
                                                </div>
                                                
                                                <!-- Play Store Link -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Play Store Link</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-surface border-default">
                                                            <i class="fab fa-google-play text-success"></i>
                                                        </span>
                                                        <input type="url" name="play_store_link" value="{{ old('play_store_link', $setting->play_store_link) }}" class="form-control" placeholder="https://play.google.com/store/apps/...">
                                                    </div>
                                                    <div class="form-text">Link to your application in the Google Play Store</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Database Management Tab -->
                                <div class="tab-pane fade" id="database" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Database Operations</h4>
                                            
                                            <div class="row g-4">
                                                <!-- Clean Database Section -->
                                                <div class="col-md-6">
                                                    <div class="card border-0 shadow-sm h-100">
                                                        <div class="card-body">
                                                            <h5 class="card-title mb-3">
                                                                <i class="fas fa-broom text-danger me-2"></i>Clean Database
                                                            </h5>
                                                            <p class="card-text">
                                                                Remove all user data while preserving essential records.
                                                                This operation will permanently delete all user data, bookings, transactions, and notifications. 
                                                                Subscription plans, features, and settings will be preserved.
                                                            </p>
                                                            
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                <strong>Warning:</strong> This action cannot be undone!
                                                            </div>
                                                            
                                                            <button type="button" class="btn btn-danger w-100" data-action="clean-database" onclick="return confirm('Are you sure you want to clean the database? This action cannot be undone!')">
                                                                <i class="fas fa-trash-alt me-1"></i> Clean Database
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Export Database Section -->
                                                <div class="col-md-6">
                                                    <div class="card border-0 shadow-sm h-100">
                                                        <div class="card-body">
                                                            <h5 class="card-title mb-3">
                                                                <i class="fas fa-file-export text-primary me-2"></i>Export Full Database
                                                            </h5>
                                                            <p class="card-text">
                                                                Download a complete backup of your entire database. 
                                                                Export your full database to a downloadable SQL file. 
                                                                This backup includes both the database structure and all data.
                                                            </p>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label fw-medium">Export Format</label>
                                                                <select class="form-select" disabled>
                                                                    <option>SQL Dump (.sql)</option>
                                                                </select>
                                                                <div class="form-text">Complete SQL backup of your database</div>
                                                            </div>
                                                            
                                                            <button type="button" class="btn btn-theme w-100" data-action="export-database">
                                                                <i class="fas fa-download me-1"></i> Export Full Database
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Frontend Settings Tab -->
                                <div class="tab-pane fade" id="frontend" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h4 class="mb-4">Frontend Access Permissions</h4>
                                            <p class="text-muted">Configure who can access your website/application.</p>
                                            
                                            <div class="card border-0 shadow-sm mb-4">
                                                <div class="card-body">
                                                    @if(empty($setting->firebase_project_id) || empty($setting->firebase_client_email) || empty($setting->firebase_private_key))
                                                        <div class="alert alert-warning mb-3">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            <strong>Firebase is not fully configured.</strong> Push notifications will not work until you complete the configuration.
                                                            <a href="{{ route('admin.firebase.setup-guide') }}" class="alert-link">View the setup guide</a> for step-by-step instructions.
                                                        </div>
                                                    @endif
                                                    <div class="row g-4">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-medium">Website Access Permission</label>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="radio" name="frontend_access_permission" id="openForAll" value="open_for_all" {{ old('frontend_access_permission', $setting->frontend_access_permission ?? 'open_for_all') == 'open_for_all' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="openForAll">
                                                                    Open for all
                                                                    <div class="form-text">Anyone can view the website/application without restrictions</div>
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="radio" name="frontend_access_permission" id="registeredUsersOnly" value="registered_users_only" {{ old('frontend_access_permission', $setting->frontend_access_permission ?? 'open_for_all') == 'registered_users_only' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="registeredUsersOnly">
                                                                    Only registered users
                                                                    <div class="form-text">Only users with valid credentials can log in and access the site</div>
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="radio" name="frontend_access_permission" id="adminApprovalRequired" value="admin_approval_required" {{ old('frontend_access_permission', $setting->frontend_access_permission ?? 'open_for_all') == 'admin_approval_required' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="adminApprovalRequired">
                                                                    User registration requires admin approval
                                                                    <div class="form-text">Users can register but must be approved by an admin before they can log in</div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-medium">Pending Approval Message</label>
                                                            <textarea name="pending_approval_message" rows="3" class="form-control" placeholder="Your account is pending approval. Please wait for admin approval before accessing the site.">{{ old('pending_approval_message', $setting->pending_approval_message ?? 'Your account is pending approval. Please wait for admin approval before accessing the site.') }}</textarea>
                                                            <div class="form-text">Message shown to users whose accounts are pending admin approval</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="card border-0 shadow-sm mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title mb-4">Payment Method Visibility</h5>
                                                    <p class="text-muted">Configure which payment methods are visible to users on the checkout page.</p>
                                                    
                                                    <div class="row g-4">
                                                        <div class="col-md-12">
                                                            <div class="form-check form-switch mb-3">
                                                                <input class="form-check-input" type="checkbox" name="show_online_payment" id="showOnlinePayment" value="1" {{ old('show_online_payment', $setting->show_online_payment ?? true) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-medium" for="showOnlinePayment">Show Online Payment Option</label>
                                                                <div class="form-text">When enabled, the "Online Payment" button will be visible on the checkout page</div>
                                                            </div>
                                                            
                                                            <div class="form-check form-switch mb-3">
                                                                <input class="form-check-input" type="checkbox" name="show_cod_payment" id="showCodPayment" value="1" {{ old('show_cod_payment', $setting->show_cod_payment ?? true) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-medium" for="showCodPayment">Show Cash on Delivery (COD) Option</label>
                                                                <div class="form-text">When enabled, the "Cash on Delivery" button will be visible on the checkout page</div>
                                                            </div>
                                                            
                                                            <div class="form-check form-switch mb-3">
                                                                <input class="form-check-input" type="checkbox" name="show_invoice_payment" id="showInvoicePayment" value="1" {{ old('show_invoice_payment', $setting->show_invoice_payment ?? true) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-medium" for="showInvoicePayment">Show Send Proforma Invoice Option</label>
                                                                <div class="form-text">When enabled, the "Send Proforma Invoice" button will be visible on the checkout page</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Subscription Plans Tab -->
                                <div class="tab-pane fade" id="subscription" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <div class="card border-0 shadow-sm mb-4">
                                                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-crown text-warning me-2"></i>Subscription Plans
                                                    </h5>
                                                    <button type="button" class="btn btn-theme btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                                                        <i class="fas fa-plus me-1"></i> Add Plan
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted mb-4">Manage subscription plans for vendors and users. Configure pricing, features, and limits for each plan.</p>
                                                    
                                                    <!-- Statistics Cards -->
                                                    <div class="row g-3 mb-4" id="subscriptionStats">
                                                        <div class="col-md-3">
                                                            <div class="card bg-primary border-0">
                                                                <div class="card-body text-center">
                                                                    <h3 class="mb-1 text-white" id="statTotalPlans">-</h3>
                                                                    <small class="text-white">Total Plans</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="card bg-success border-0">
                                                                <div class="card-body text-center">
                                                                    <h3 class="mb-1 text-white" id="statActivePlans">-</h3>
                                                                    <small class="text-white">Active Plans</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="card bg-info border-0">
                                                                <div class="card-body text-center">
                                                                    <h3 class="mb-1 text-white" id="statActiveSubscriptions">-</h3>
                                                                    <small class="text-white">Active Subscriptions</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="card bg-warning border-0">
                                                                <div class="card-body text-center">
                                                                    <h3 class="mb-1 text-white" id="statMonthlyRevenue">-</h3>
                                                                    <small class="text-white">This Month Revenue</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Plans Table -->
                                                    <div class="table-responsive">
                                                        <table class="table table-hover" id="subscriptionPlansTable">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th width="5%">#</th>
                                                                    <th width="25%">Plan Name</th>
                                                                    <th width="20%">Price</th>
                                                                    <th width="15%">Billing</th>
                                                                    <th width="15%">Status</th>
                                                                    <th width="20%">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="plansTableBody">
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
                                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                        <span class="ms-2">Loading plans...</span>
                                                    </td>
                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 d-flex justify-content-between">
                                <span></span>
                                <button type="submit" class="btn btn-theme rounded-pill px-4">
                                    <i class="fas fa-save me-1"></i> Save Settings
                                </button>
                            </div>
                        </form>
                        
                        <!-- Hidden forms for database management (outside the main form) -->
                        <div style="display: none;">
                            <form id="cleanDatabaseForm" action="{{ route('admin.settings.database.clean') }}" method="POST">
                                @csrf
                            </form>
                            <form id="exportDatabaseForm" action="{{ route('admin.settings.database.export') }}" method="POST">
                                @csrf
                            </form>
                        </div>
                        
                        <!-- Add/Edit Subscription Plan Modal -->
                        <div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addPlanModalLabel">
                                            <i class="fas fa-crown text-warning me-2"></i>Add Subscription Plan
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="planForm">
                                            <input type="hidden" id="planId" name="plan_id">
                                            
                                            <div class="row g-3">
                                                <!-- Basic Info -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Plan Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="planName" name="name" required placeholder="e.g., Professional">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Price (₹) <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="planPrice" name="price" required min="0" step="0.01" placeholder="0.00">
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Billing Cycle <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="planBillingCycle" name="billing_cycle" required>
                                                        <option value="monthly">Monthly</option>
                                                        <option value="quarterly">Quarterly</option>
                                                        <option value="yearly">Yearly</option>
                                                        <option value="lifetime">Lifetime</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Duration (Days) <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="planDuration" name="duration_days" required min="1" value="30">
                                                </div>
                                                
                                                <div class="col-12">
                                                    <label class="form-label fw-medium">Description</label>
                                                    <textarea class="form-control" id="planDescription" name="description" rows="2" placeholder="Brief description of the plan"></textarea>
                                                </div>
                                                
                                                <!-- Trial Days -->
                                                <div class="col-12">
                                                    <hr class="my-2">
                                                    <h6 class="fw-semibold mb-3"><i class="fas fa-calendar-check me-2"></i>Trial Period</h6>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label class="form-label">Trial Days</label>
                                                    <input type="number" class="form-control" id="planTrialDays" name="trial_days" min="0" value="0" placeholder="0 for no trial">
                                                </div>
                                                
                                                <!-- Features -->
                                                <div class="col-12">
                                                    <hr class="my-2">
                                                    <h6 class="fw-semibold mb-3"><i class="fas fa-list-check me-2"></i>Features</h6>
                                                </div>
                                                <div class="col-12">
                                                    <div id="featuresContainer">
                                                        <div class="input-group mb-2 feature-row">
                                                            <input type="text" class="form-control feature-input" name="features[]" placeholder="Enter a feature">
                                                            <button type="button" class="btn btn-outline-danger remove-feature" disabled>
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addFeatureBtn">
                                                        <i class="fas fa-plus me-1"></i> Add Feature
                                                    </button>
                                                </div>
                                                
                                                <!-- Options -->
                                                <div class="col-12">
                                                    <hr class="my-2">
                                                    <h6 class="fw-semibold mb-3"><i class="fas fa-cog me-2"></i>Options</h6>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Sort Order</label>
                                                    <input type="number" class="form-control" id="planSortOrder" name="sort_order" min="0" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Discount (%)</label>
                                                    <input type="number" class="form-control" id="planDiscount" name="discount_percentage" min="0" max="100" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4 d-flex align-items-end">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="planIsActive" name="is_active" checked>
                                                        <label class="form-check-label" for="planIsActive">Active</label>
                                                    </div>
                                                    <div class="form-check form-switch ms-3">
                                                        <input class="form-check-input" type="checkbox" id="planIsFeatured" name="is_featured">
                                                        <label class="form-check-label" for="planIsFeatured">Featured</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-theme rounded-pill" id="savePlanBtn">
                                            <i class="fas fa-save me-1"></i> Save Plan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- View Plan Details Modal -->
                        <div class="modal fade" id="viewPlanModal" tabindex="-1" aria-labelledby="viewPlanModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewPlanModalLabel">
                                            <i class="fas fa-crown text-warning me-2"></i>Plan Details
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="viewPlanContent">
                                        <!-- Content will be loaded dynamically -->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                                    </div>
                                    <!-- End subscription tab-pane -->
                                    </div>
                                    <!-- End tab-content -->
                                </div>
                                <!-- End settings-content -->
                            </div>
                            <!-- End settings-container -->
                        </form>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<script>
    document.getElementById('resetButton').addEventListener('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
            document.getElementById('resetForm').submit();
        }
    });
    
    // Add mutual exclusivity logic for site management modes
    document.addEventListener('DOMContentLoaded', function() {
        const maintenanceModeCheckbox = document.getElementById('maintenanceMode');
        const comingSoonModeCheckbox = document.getElementById('comingSoonMode');
        
        // When maintenance mode is enabled, disable coming soon mode
        maintenanceModeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                comingSoonModeCheckbox.checked = false;
            }
        });
        
        // When coming soon mode is enabled, disable maintenance mode
        comingSoonModeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                maintenanceModeCheckbox.checked = false;
            }
        });
        
        // Check for password-related errors or fragment
        const urlFragment = window.location.hash;
        const hasPasswordErrors = document.querySelectorAll('#password .is-invalid, #password .invalid-feedback').length > 0;
        
        if (urlFragment === '#password' || hasPasswordErrors) {
            // Switch to password tab using Bootstrap tab functionality
            const passwordTab = new bootstrap.Tab(document.getElementById('password-tab'));
            passwordTab.show();
            document.getElementById('activeTabInput').value = 'password';
        }
        
        // Handle tab switching and sync mobile dropdown
        const tabButtons = document.querySelectorAll('#settingsTabs [data-bs-toggle="tab"]');
        const mobileDropdownItems = document.querySelectorAll('.settings-dropdown-menu .dropdown-item');
        const currentTabLabel = document.getElementById('currentTabLabel');
        const dropdownBtn = document.querySelector('.settings-dropdown-btn');
        
        // Tab label mapping
        const tabLabels = {
            'general': 'General',
            'social': 'Social Media',
            'appearance': 'Appearance',
            'site-management': 'Site Management',
            'payment': 'Payment',
            'notifications': 'Notifications',
            'application': 'Application Links',
            'database': 'Database Management',
            'frontend': 'Frontend Settings',
            'subscription': 'Subscription Plans'
        };
        
        // Tab icon mapping
        const tabIcons = {
            'general': 'fa-cog',
            'social': 'fa-hashtag',
            'appearance': 'fa-palette',
            'site-management': 'fa-server',
            'payment': 'fa-credit-card',
            'notifications': 'fa-bell',
            'application': 'fa-mobile-alt',
            'database': 'fa-database',
            'frontend': 'fa-window-maximize',
            'subscription': 'fa-crown'
        };
        
        tabButtons.forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                const tabId = e.target.id.replace('-tab', '');
                document.getElementById('activeTabInput').value = tabId;
                
                // Update mobile dropdown button text and icon
                if (currentTabLabel) {
                    currentTabLabel.textContent = tabLabels[tabId] || tabId;
                }
                if (dropdownBtn) {
                    const iconEl = dropdownBtn.querySelector('.current-tab i');
                    if (iconEl) {
                        iconEl.className = 'fas ' + (tabIcons[tabId] || 'fa-cog');
                    }
                }
                
                // Update active state in mobile dropdown
                mobileDropdownItems.forEach(function(item) {
                    item.classList.remove('active');
                    if (item.dataset.tab === e.target.id) {
                        item.classList.add('active');
                    }
                });
            });
        });
        
        // Handle mobile dropdown item clicks
        mobileDropdownItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.dataset.tab;
                const tabButton = document.getElementById(tabId);
                if (tabButton) {
                    const tab = new bootstrap.Tab(tabButton);
                    tab.show();
                }
            });
        });
        
        // Restore active tab from session
        const activeTab = "{{ session('tab', '') }}";
        if (activeTab) {
            const tab = document.getElementById(activeTab + '-tab');
            if (tab) {
                const tabInstance = new bootstrap.Tab(tab);
                tabInstance.show();
                document.getElementById('activeTabInput').value = activeTab;
            }
        }
        
        // Add event listeners for database management buttons
        const cleanDatabaseButton = document.querySelector('[data-action="clean-database"]');
        const exportDatabaseButton = document.querySelector('[data-action="export-database"]');
        
        if (cleanDatabaseButton) {
            cleanDatabaseButton.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to clean the database? This action cannot be undone!')) {
                    document.getElementById('cleanDatabaseForm').submit();
                }
            });
        }
        
        if (exportDatabaseButton) {
            exportDatabaseButton.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('exportDatabaseForm').submit();
            });
        }
        
        // Password visibility toggle functionality
        const togglePasswordButtons = document.querySelectorAll('.toggle-password');
        togglePasswordButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
    
    // Firebase Configuration Test
    document.getElementById('testFirebaseConfig')?.addEventListener('click', function() {
        // Show the modal
        var testModal = new bootstrap.Modal(document.getElementById('firebaseTestModal'));
        testModal.show();
        
        // Make AJAX request to test Firebase configuration
        fetch('{{ route("admin.firebase.test") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            let resultHtml = `
                <div class="alert alert-${data.success ? 'success' : 'danger'}">
                    <h5><i class="fas ${data.success ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>${data.success ? 'Test Successful' : 'Test Failed'}</h5>
                    <p>${data.message}</p>
                </div>
            `;
            
            if (data.details) {
                resultHtml += `
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Configuration Details</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Project ID
                                    <span class="badge bg-${data.details.project_id ? 'success' : 'danger'} rounded-pill">
                                        ${data.details.project_id ? 'Configured' : 'Missing'}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Client Email
                                    <span class="badge bg-${data.details.client_email ? 'success' : 'danger'} rounded-pill">
                                        ${data.details.client_email ? 'Configured' : 'Missing'}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Private Key
                                    <span class="badge bg-${data.details.private_key ? 'success' : 'danger'} rounded-pill">
                                        ${data.details.private_key ? 'Configured' : 'Missing'}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                `;
            }
            
            document.getElementById('testResults').innerHTML = resultHtml;
        })
        .catch(error => {
            document.getElementById('testResults').innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-circle me-2"></i>Error</h5>
                    <p>Failed to test configuration: ${error.message}</p>
                </div>
            `;
        });
    });
    
    // View Firebase Statistics
    document.getElementById('viewFirebaseStats')?.addEventListener('click', function() {
        // Show the modal
        var statsModal = new bootstrap.Modal(document.getElementById('firebaseStatsModal'));
        statsModal.show();
        
        // Make AJAX request to get Firebase statistics
        fetch('{{ route("admin.firebase.stats") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            let statsHtml = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="card-title">${data.total_sent || 0}</h3>
                                <p class="card-text">Notifications Sent</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="card-title">${data.total_delivered || 0}</h3>
                                <p class="card-text">Delivered</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="card-title">${data.total_failed || 0}</h3>
                                <p class="card-text">Failed</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Recent Activity</h6>
                    </div>
                    <div class="card-body">
                        ${data.recent_activity && data.recent_activity.length > 0 ? 
                            `<div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.recent_activity.map(activity => `
                                            <tr>
                                                <td>${activity.date}</td>
                                                <td>${activity.type}</td>
                                                <td>
                                                    <span class="badge bg-${activity.status === 'delivered' ? 'success' : activity.status === 'failed' ? 'danger' : 'warning'}">
                                                        ${activity.status}
                                                    </span>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>` : 
                            '<p class="text-muted text-center">No recent activity</p>'
                        }
                    </div>
                </div>
            `;
            
            document.getElementById('statsContent').innerHTML = statsHtml;
        })
        .catch(error => {
            document.getElementById('statsContent').innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-circle me-2"></i>Error</h5>
                    <p>Failed to load statistics: ${error.message}</p>
                </div>
            `;
        });
    });
    
    // ==========================================
    // SUBSCRIPTION PLANS MANAGEMENT
    // ==========================================
    
    // Load subscription plans when tab is shown
    document.getElementById('subscription-tab')?.addEventListener('shown.bs.tab', function() {
        loadSubscriptionPlans();
        loadSubscriptionStats();
    });
    
    // Also load if subscription tab is active on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('subscription-tab')?.classList.contains('active')) {
            loadSubscriptionPlans();
            loadSubscriptionStats();
        }
    });
    
    // Load subscription statistics
    function loadSubscriptionStats() {
        fetch('{{ route("admin.subscription-plans.statistics") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('statTotalPlans').textContent = data.statistics.total_plans || 0;
                document.getElementById('statActivePlans').textContent = data.statistics.active_plans || 0;
                document.getElementById('statActiveSubscriptions').textContent = data.statistics.active_subscriptions || 0;
                document.getElementById('statMonthlyRevenue').textContent = '₹' + (data.statistics.revenue_this_month || 0).toLocaleString();
            }
        })
        .catch(error => {
            console.error('Error loading subscription stats:', error);
        });
    }
    
    // Load subscription plans
    function loadSubscriptionPlans() {
        const tbody = document.getElementById('plansTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading plans...</span>
                </td>
            </tr>
        `;
        
        fetch('{{ route("admin.subscription-plans.index") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.plans.length > 0) {
                let html = '';
                data.plans.forEach((plan, index) => {
                    const features = plan.features || [];
                    
                    html += `
                        <tr data-plan-id="${plan.id}">
                            <td>${index + 1}</td>
                            <td>
                                <strong>${plan.name}</strong>
                                ${plan.is_featured ? '<span class="badge bg-warning ms-1"><i class="fas fa-star"></i></span>' : ''}
                                ${plan.trial_days > 0 ? `<br><small class="text-muted">${plan.trial_days} days trial</small>` : ''}
                            </td>
                            <td>
                                <strong class="text-success">₹${parseFloat(plan.price).toLocaleString()}</strong>
                                ${plan.discount_percentage > 0 ? `<br><small class="text-danger">${plan.discount_percentage}% off</small>` : ''}
                            </td>
                            <td>
                                <span class="badge bg-secondary">${plan.billing_cycle}</span>
                                <br><small class="text-muted">${plan.duration_days} days</small>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input plan-status-toggle" type="checkbox" 
                                        data-plan-id="${plan.id}" ${plan.is_active ? 'checked' : ''}>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info view-plan-btn" data-plan='${JSON.stringify(plan)}' title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary edit-plan-btn" data-plan='${JSON.stringify(plan)}' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-plan-btn" data-plan-id="${plan.id}" data-plan-name="${plan.name}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
                
                // Attach event listeners
                attachPlanEventListeners();
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-crown fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No subscription plans found. Click "Add Plan" to create one.</p>
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>Failed to load plans. Please try again.
                    </td>
                </tr>
            `;
            console.error('Error loading plans:', error);
        });
    }
    
    // Attach event listeners to plan buttons
    function attachPlanEventListeners() {
        // View plan buttons
        document.querySelectorAll('.view-plan-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const plan = JSON.parse(this.getAttribute('data-plan'));
                showPlanDetails(plan);
            });
        });
        
        // Edit plan buttons
        document.querySelectorAll('.edit-plan-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const plan = JSON.parse(this.getAttribute('data-plan'));
                editPlan(plan);
            });
        });
        
        // Delete plan buttons
        document.querySelectorAll('.delete-plan-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const planId = this.getAttribute('data-plan-id');
                const planName = this.getAttribute('data-plan-name');
                deletePlan(planId, planName);
            });
        });
        
        // Status toggle switches
        document.querySelectorAll('.plan-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const planId = this.getAttribute('data-plan-id');
                togglePlanStatus(planId, this);
            });
        });
    }
    
    // Show plan details modal
    function showPlanDetails(plan) {
        const features = plan.features || [];
        const featuresHtml = features.length > 0 
            ? features.map(f => `<li><i class="fas fa-check text-success me-2"></i>${f}</li>`).join('')
            : '<li class="text-muted">No features defined</li>';
        
        const content = `
            <div class="text-center mb-4">
                <h4 class="mb-1">${plan.name}</h4>
                ${plan.is_featured ? '<span class="badge bg-warning"><i class="fas fa-star me-1"></i>Featured</span>' : ''}
                <p class="text-muted mb-0">${plan.description || 'No description'}</p>
            </div>
            
            <div class="text-center mb-4">
                <h2 class="text-success mb-0">₹${parseFloat(plan.price).toLocaleString()}</h2>
                <small class="text-muted">per ${plan.billing_cycle} (${plan.duration_days} days)</small>
                ${plan.discount_percentage > 0 ? `<br><span class="badge bg-danger">${plan.discount_percentage}% discount</span>` : ''}
                ${plan.trial_days > 0 ? `<br><span class="badge bg-info mt-1">${plan.trial_days} days free trial</span>` : ''}
            </div>
            
            <h6 class="fw-semibold mb-3"><i class="fas fa-list-check me-2"></i>Features</h6>
            <ul class="list-unstyled mb-0">${featuresHtml}</ul>
        `;
        
        document.getElementById('viewPlanContent').innerHTML = content;
        const modal = new bootstrap.Modal(document.getElementById('viewPlanModal'));
        modal.show();
    }
    
    // Edit plan - populate form and show modal
    function editPlan(plan) {
        document.getElementById('addPlanModalLabel').innerHTML = '<i class="fas fa-crown text-warning me-2"></i>Edit Subscription Plan';
        document.getElementById('planId').value = plan.id;
        document.getElementById('planName').value = plan.name;
        document.getElementById('planPrice').value = plan.price;
        document.getElementById('planBillingCycle').value = plan.billing_cycle;
        document.getElementById('planDuration').value = plan.duration_days;
        document.getElementById('planDescription').value = plan.description || '';
        document.getElementById('planTrialDays').value = plan.trial_days || 0;
        document.getElementById('planSortOrder').value = plan.sort_order || 0;
        document.getElementById('planDiscount').value = plan.discount_percentage || 0;
        document.getElementById('planIsActive').checked = plan.is_active;
        document.getElementById('planIsFeatured').checked = plan.is_featured;
        
        // Populate features
        const featuresContainer = document.getElementById('featuresContainer');
        featuresContainer.innerHTML = '';
        const features = plan.features || [];
        if (features.length > 0) {
            features.forEach((feature, index) => {
                addFeatureRow(feature, index === 0);
            });
        } else {
            addFeatureRow('', true);
        }
        
        const modal = new bootstrap.Modal(document.getElementById('addPlanModal'));
        modal.show();
    }
    
    // Delete plan
    function deletePlan(planId, planName) {
        if (!confirm(`Are you sure you want to delete the "${planName}" plan? This action cannot be undone.`)) {
            return;
        }
        
        fetch(`{{ url('admin/subscription-plans') }}/${planId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                loadSubscriptionPlans();
                loadSubscriptionStats();
            } else {
                showToast('error', data.message);
            }
        })
        .catch(error => {
            showToast('error', 'Failed to delete plan. Please try again.');
            console.error('Error deleting plan:', error);
        });
    }
    
    // Toggle plan status
    function togglePlanStatus(planId, toggleElement) {
        fetch(`{{ url('admin/subscription-plans') }}/${planId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                loadSubscriptionStats();
            } else {
                toggleElement.checked = !toggleElement.checked;
                showToast('error', data.message);
            }
        })
        .catch(error => {
            toggleElement.checked = !toggleElement.checked;
            showToast('error', 'Failed to update plan status.');
            console.error('Error toggling plan status:', error);
        });
    }
    
    // Add feature row
    function addFeatureRow(value = '', isFirst = false) {
        const container = document.getElementById('featuresContainer');
        const row = document.createElement('div');
        row.className = 'input-group mb-2 feature-row';
        row.innerHTML = `
            <input type="text" class="form-control feature-input" name="features[]" placeholder="Enter a feature" value="${value}">
            <button type="button" class="btn btn-outline-danger remove-feature" ${isFirst ? 'disabled' : ''}>
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(row);
        
        // Attach remove event
        row.querySelector('.remove-feature').addEventListener('click', function() {
            row.remove();
            updateRemoveButtons();
        });
    }
    
    // Update remove buttons state
    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.feature-row');
        rows.forEach((row, index) => {
            const btn = row.querySelector('.remove-feature');
            btn.disabled = rows.length === 1;
        });
    }
    
    // Add feature button click
    document.getElementById('addFeatureBtn')?.addEventListener('click', function() {
        addFeatureRow();
        updateRemoveButtons();
    });
    
    // Reset form when modal is hidden
    document.getElementById('addPlanModal')?.addEventListener('hidden.bs.modal', function() {
        document.getElementById('addPlanModalLabel').innerHTML = '<i class="fas fa-crown text-warning me-2"></i>Add Subscription Plan';
        document.getElementById('planForm').reset();
        document.getElementById('planId').value = '';
        
        // Reset features to single empty row
        const featuresContainer = document.getElementById('featuresContainer');
        featuresContainer.innerHTML = '';
        addFeatureRow('', true);
    });
    
    // Save plan button click
    document.getElementById('savePlanBtn')?.addEventListener('click', function() {
        const form = document.getElementById('planForm');
        const planId = document.getElementById('planId').value;
        
        // Gather form data
        const formData = {
            name: document.getElementById('planName').value,
            price: document.getElementById('planPrice').value,
            billing_cycle: document.getElementById('planBillingCycle').value,
            duration_days: document.getElementById('planDuration').value,
            description: document.getElementById('planDescription').value,
            trial_days: document.getElementById('planTrialDays').value || 0,
            sort_order: document.getElementById('planSortOrder').value || 0,
            discount_percentage: document.getElementById('planDiscount').value || 0,
            is_active: document.getElementById('planIsActive').checked,
            is_featured: document.getElementById('planIsFeatured').checked,
            features: []
        };
        
        // Gather features
        document.querySelectorAll('.feature-input').forEach(input => {
            if (input.value.trim()) {
                formData.features.push(input.value.trim());
            }
        });
        
        // Validate required fields
        if (!formData.name || !formData.price || !formData.billing_cycle || !formData.duration_days) {
            showToast('error', 'Please fill in all required fields.');
            return;
        }
        
        const url = planId 
            ? `{{ url('admin/subscription-plans') }}/${planId}` 
            : '{{ route("admin.subscription-plans.store") }}';
        const method = planId ? 'PUT' : 'POST';
        
        // Disable button during request
        const saveBtn = document.getElementById('savePlanBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Plan';
            
            if (data.success) {
                showToast('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('addPlanModal')).hide();
                loadSubscriptionPlans();
                loadSubscriptionStats();
            } else {
                showToast('error', data.message || 'Failed to save plan.');
            }
        })
        .catch(error => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Plan';
            showToast('error', 'Failed to save plan. Please try again.');
            console.error('Error saving plan:', error);
        });
    });
    
    // Toast notification helper
    function showToast(type, message) {
        // Create toast container if not exists
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
        const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
        
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${icon} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }
</script>

<!-- Flatpickr JS for datetime picker -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Initialize datetime pickers for Site Management
    document.addEventListener('DOMContentLoaded', function() {
        // Common Flatpickr configuration
        const flatpickrConfig = {
            enableTime: true,
            dateFormat: "d/m/Y H:i",
            time_24hr: true,
            allowInput: true,
            minDate: "today",
            theme: "material_blue"
        };
        
        // Initialize Maintenance End Time picker
        const maintenanceEndTimeInput = document.getElementById('maintenanceEndTime');
        if (maintenanceEndTimeInput) {
            flatpickr(maintenanceEndTimeInput, flatpickrConfig);
        }
        
        // Initialize Launch Time picker
        const launchTimeInput = document.getElementById('launchTime');
        if (launchTimeInput) {
            flatpickr(launchTimeInput, flatpickrConfig);
        }
    });
</script>
@endsection