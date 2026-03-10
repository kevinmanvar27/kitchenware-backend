@extends('vendor.layouts.app')

@section('title', 'Store Settings')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Store Settings'])
            
            @section('page-title', 'Store Settings')
            
            <div class="pt-4 pb-2 mb-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="{{ route('vendor.profile.store-settings') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Store Branding -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-palette me-2 text-primary"></i>Store Branding
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <label for="banner" class="form-label fw-bold">Store Banner (Upload)</label>
                                        @if($vendor && $vendor->store_banner_url)
                                            <div class="mb-2">
                                                <img src="{{ $vendor->store_banner_url }}" alt="Store Banner" class="img-fluid rounded" style="max-height: 150px;">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" id="banner" name="banner" accept="image/*">
                                        <div class="form-text">Recommended size: 1200x300 pixels. Upload a file to use a local banner.</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="banner_image_url" class="form-label fw-bold">Store Banner Image URL (External)</label>
                                        <input type="url" class="form-control rounded-pill px-4" id="banner_image_url" name="banner_image_url" value="{{ old('banner_image_url', $vendor->banner_image_url ?? '') }}" placeholder="https://example.com/banner.jpg">
                                        <div class="form-text">Or provide an external URL for the banner image. This will override the uploaded file.</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="banner_redirect_url" class="form-label fw-bold">
                                            <i class="fas fa-link me-2 text-primary"></i>Banner Redirect URL
                                        </label>
                                        <input type="url" class="form-control rounded-pill px-4" id="banner_redirect_url" name="banner_redirect_url" value="{{ old('banner_redirect_url', $vendor->banner_redirect_url ?? '') }}" placeholder="https://hardware.rektech.work/api/v1/customer/products/20">
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            URL where customers will be redirected when they click on the banner (e.g., product page, category page, etc.)
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="tagline" class="form-label fw-bold">Store Tagline</label>
                                        <input type="text" class="form-control rounded-pill px-4" id="tagline" name="tagline" value="{{ old('tagline', $settings['tagline'] ?? '') }}" placeholder="Your catchy tagline here">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label fw-bold">Meta Title (SEO)</label>
                                        <input type="text" class="form-control rounded-pill px-4" id="meta_title" name="meta_title" value="{{ old('meta_title', $settings['meta_title'] ?? '') }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label fw-bold">Meta Description (SEO)</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Social Media -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-share-alt me-2 text-primary"></i>Social Media Links
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="facebook" class="form-label fw-bold">
                                            <i class="fab fa-facebook text-primary me-2"></i>Facebook
                                        </label>
                                        <input type="url" class="form-control rounded-pill px-4" id="facebook" name="social_facebook" value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}" placeholder="https://facebook.com/yourpage">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="instagram" class="form-label fw-bold">
                                            <i class="fab fa-instagram text-danger me-2"></i>Instagram
                                        </label>
                                        <input type="url" class="form-control rounded-pill px-4" id="instagram" name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}" placeholder="https://instagram.com/yourpage">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="twitter" class="form-label fw-bold">
                                            <i class="fab fa-twitter text-info me-2"></i>Twitter/X
                                        </label>
                                        <input type="url" class="form-control rounded-pill px-4" id="twitter" name="social_twitter" value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}" placeholder="https://twitter.com/yourpage">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="youtube" class="form-label fw-bold">
                                            <i class="fab fa-youtube text-danger me-2"></i>YouTube
                                        </label>
                                        <input type="url" class="form-control rounded-pill px-4" id="youtube" name="social_youtube" value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}" placeholder="https://youtube.com/yourchannel">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="website" class="form-label fw-bold">
                                            <i class="fas fa-globe text-success me-2"></i>Website
                                        </label>
                                        <input type="url" class="form-control rounded-pill px-4" id="website" name="website" value="{{ old('website', $settings['website'] ?? '') }}" placeholder="https://yourwebsite.com">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Business Hours -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-clock me-2 text-primary"></i>Business Hours
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                    @endphp
                                    
                                    @foreach($days as $day)
                                    <div class="row mb-2 align-items-center">
                                        <div class="col-4">
                                            <label class="form-label mb-0 fw-bold text-capitalize">{{ $day }}</label>
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control form-control-sm" name="hours_{{ $day }}_open" value="{{ old('hours_'.$day.'_open', $settings['hours_'.$day.'_open'] ?? '09:00') }}">
                                        </div>
                                        <div class="col-4">
                                            <input type="time" class="form-control form-control-sm" name="hours_{{ $day }}_close" value="{{ old('hours_'.$day.'_close', $settings['hours_'.$day.'_close'] ?? '18:00') }}">
                                        </div>
                                    </div>
                                    @endforeach
                                    
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" name="show_business_hours" id="show_business_hours" value="1" {{ old('show_business_hours', $settings['show_business_hours'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_business_hours">
                                            Display business hours on store page
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Policies -->
                        <div class="col-lg-12 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-file-alt me-2 text-primary"></i>Store Policies
                                    </h5>
                                    <p class="text-muted small mb-0">Use the rich text editor to format your policies with headings, lists, and styling</p>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <label for="return_policy" class="form-label fw-bold">Return Policy</label>
                                        <div class="quill-editor-wrapper">
                                            <div id="return_policy_editor" class="quill-editor"></div>
                                            <textarea class="quill-hidden-textarea" id="return_policy" name="return_policy">{{ old('return_policy', $settings['return_policy'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="shipping_policy" class="form-label fw-bold">Shipping Policy</label>
                                        <div class="quill-editor-wrapper">
                                            <div id="shipping_policy_editor" class="quill-editor"></div>
                                            <textarea class="quill-hidden-textarea" id="shipping_policy" name="shipping_policy">{{ old('shipping_policy', $settings['shipping_policy'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="terms_conditions" class="form-label fw-bold">Terms & Conditions</label>
                                        <div class="quill-editor-wrapper">
                                            <div id="terms_conditions_editor" class="quill-editor"></div>
                                            <textarea class="quill-hidden-textarea" id="terms_conditions" name="terms_conditions">{{ old('terms_conditions', $settings['terms_conditions'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-theme btn-lg rounded-pill px-5">
                                    <i class="fas fa-save me-2"></i>Save All Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Feature Settings Link -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div>
                                    <h5 class="mb-1 fw-bold">
                                        <i class="fas fa-toggle-on me-2 text-primary"></i>Feature Settings
                                    </h5>
                                    <p class="text-muted mb-0 small">Enable or disable optional features for your store</p>
                                </div>
                                <a href="{{ route('vendor.feature-settings.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                                    <i class="fas fa-cog me-2"></i>Manage Features
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection

@push('styles')
<!-- Quill Editor CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .quill-editor-wrapper {
        margin-bottom: 1rem;
    }
    
    .quill-editor {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
    
    .ql-toolbar.ql-snow {
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
    }
    
    .ql-container.ql-snow {
        border-bottom-left-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
        border: 1px solid #dee2e6;
        border-top: none;
        min-height: 300px;
        font-size: 14px;
    }
    
    .ql-editor {
        min-height: 300px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 14px;
        line-height: 1.6;
    }
    
    .ql-editor.ql-blank::before {
        color: #6c757d;
        font-style: normal;
    }
    
    /* Hide the original textarea */
    .quill-hidden-textarea {
        display: none;
    }
</style>
@endpush

@push('scripts')
<!-- Quill Editor JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill editors for all policy textareas
    const editorConfigs = [
        { textareaId: 'return_policy', editorId: 'return_policy_editor' },
        { textareaId: 'shipping_policy', editorId: 'shipping_policy_editor' },
        { textareaId: 'terms_conditions', editorId: 'terms_conditions_editor' }
    ];
    
    editorConfigs.forEach(config => {
        const textarea = document.getElementById(config.textareaId);
        const editorContainer = document.getElementById(config.editorId);
        
        if (textarea && editorContainer) {
            // Initialize Quill
            const quill = new Quill(editorContainer, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'align': [] }],
                        ['link', 'blockquote'],
                        ['clean']
                    ]
                },
                placeholder: 'Enter your policy details here...'
            });
            
            // Set initial content from textarea
            if (textarea.value) {
                quill.root.innerHTML = textarea.value;
            }
            
            // Update textarea when content changes
            quill.on('text-change', function() {
                textarea.value = quill.root.innerHTML;
            });
            
            // Update Quill when textarea changes (for form validation)
            textarea.addEventListener('input', function() {
                if (quill.root.innerHTML !== textarea.value) {
                    quill.root.innerHTML = textarea.value;
                }
            });
        }
    });
    
    // Ensure textareas are updated before form submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            // Quill automatically updates the textarea, but we'll ensure it's done
            editorConfigs.forEach(config => {
                const textarea = document.getElementById(config.textareaId);
                const editorContainer = document.getElementById(config.editorId);
                if (textarea && editorContainer) {
                    const quillEditor = editorContainer.__quill;
                    if (quillEditor) {
                        textarea.value = quillEditor.root.innerHTML;
                    }
                }
            });
        });
    }
});
</script>
@endpush
